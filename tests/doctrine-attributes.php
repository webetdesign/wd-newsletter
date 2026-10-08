<?php

declare(strict_types=1);

/**
 * No kernel, SQL, schema generation or mail. The DBAL connection stays unopened.
 * php tests/doctrine-attributes.php /absolute/app/vendor/autoload.php [baseline.json]
 * Capture the pre-conversion baseline in a separate process, using stable vendor:
 * php tests/doctrine-attributes.php /absolute/app/vendor/autoload.php output.json --baseline-vendor
 * Use --baseline-candidate before conversion to prove the candidate has the same mapping.
 */
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Knp\DoctrineBehaviors\EventSubscriber\TranslatableEventSubscriber;

$autoload = $argv[1] ?? '';
if (!is_file($autoload)) {
    fwrite(STDERR, "Usage: php {$argv[0]} /absolute/app/vendor/autoload.php [baseline.json] [--baseline-vendor|--baseline-candidate]\n");
    exit(2);
}
$loader = require $autoload;
$mode = $argv[3] ?? '';
$capturing = in_array($mode, ['--baseline-vendor', '--baseline-candidate'], true);
$annotations = $capturing;
$baselinePath = $argv[2] ?? __DIR__ . '/doctrine-baseline.json';
$prefix = 'WebEtDesign\\NewsletterBundle\\';
$root = dirname(__DIR__);
if ($mode !== '--baseline-vendor') {
    spl_autoload_register(static function (string $class) use ($prefix, $root): void {
        if (str_starts_with($class, $prefix)) {
            $file = $root . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    }, true, true);
}

$passed = 0;
$failed = 0;
function check(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        ++$passed;
        echo "PASS $label\n";
    } else {
        ++$failed;
        echo "FAIL $label\n";
    }
}
function canonical(mixed $value): mixed
{
    if (is_object($value)) {
        $value = get_object_vars($value);
    }
    if (!is_array($value)) {
        return $value;
    }
    if (!array_is_list($value)) {
        ksort($value);
    }
    return array_map('canonical', $value);
}
function snapshot(ClassMetadata $metadata): array
{
    $result = [];
    foreach (get_object_vars($metadata) as $key => $value) {
        // Reflection is runtime state, not mapping. Keep every scalar/array mapping property.
        if (!in_array($key, ['reflClass', 'reflFields'], true) && !is_object($value)) {
            $result[$key] = canonical($value);
        }
    }
    return canonical($result);
}

try {
    $driver = $annotations
        ? new Doctrine\ORM\Mapping\Driver\AnnotationDriver(new Doctrine\Common\Annotations\AnnotationReader())
        : new AttributeDriver([$root . '/src/Entity']);
    $metadata = [];
    foreach (['Newsletter', 'Content', 'ContentTranslation', 'NewsletterLog'] as $short) {
        $class = $prefix . 'Entity\\' . $short;
        $reflection = new ReflectionClass($class);
        $expectedFile = $mode === '--baseline-vendor' ? $loader->findFile($class) : $root . '/src/Entity/' . $short . '.php';
        check(realpath($reflection->getFileName()) === realpath($expectedFile), "$short loaded from intended source");
        $meta = new ClassMetadata($class);
        $meta->initializeReflection(new RuntimeReflectionService());
        try {
            $driver->loadMetadataForClass($class, $meta);
            check(true, "$short mapped by " . ($annotations ? 'AnnotationDriver' : 'AttributeDriver'));
            $metadata[$short] = $meta;
        } catch (Throwable $error) {
            check(false, "$short mapping: " . $error->getMessage());
        }
    }

    if (count($metadata) === 4) {
        $config = new Configuration();
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(__DIR__);
        $config->setProxyNamespace('NewsletterMetadataTestProxies');
        // Explicit platform version avoids server version detection; no connect() or SQL.
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $manager = new EntityManager($connection, $config);
        foreach ($metadata as $meta) {
            $manager->getMetadataFactory()->setMetadataFor($meta->name, $meta);
        }
        $localeProvider = new class implements LocaleProviderInterface {
            public function provideCurrentLocale(): ?string { return 'fr'; }
            public function provideFallbackLocale(): ?string { return 'en'; }
        };
        $subscriber = new TranslatableEventSubscriber($localeProvider, 'LAZY', 'LAZY');
        foreach ($metadata as $meta) {
            $subscriber->loadClassMetadata(new LoadClassMetadataEventArgs($meta, $manager));
        }
        check(!$connection->isConnected(), 'KNP metadata listener ran without opening a database connection');
        $n = $metadata['Newsletter'];
        $c = $metadata['Content'];
        $t = $metadata['ContentTranslation'];
        $l = $metadata['NewsletterLog'];
        foreach (['Newsletter' => 'newsletter__newsletter', 'Content' => 'newsletter__content', 'ContentTranslation' => 'newsletter__content_translation', 'NewsletterLog' => 'newsletter__log'] as $short => $table) {
            check($metadata[$short]->getTableName() === $table, "$short table preserved");
            check($metadata[$short]->identifier === ['id'] && $metadata[$short]->generatorType === ClassMetadata::GENERATOR_TYPE_AUTO, "$short generated identifier preserved");
        }
        check($n->associationMappings['contents']['orderBy'] === ['position' => 'ASC'], 'contents OrderBy position ASC');
        check($n->associationMappings['contents']['cascade'] === ['persist', 'remove'] && $n->associationMappings['contents']['orphanRemoval'], 'contents cascade and orphanRemoval');
        check($n->associationMappings['groups']['targetEntity'] === 'App\\Entity\\User\\Group', 'application Group relation');
        $join = $n->associationMappings['groups']['joinTable'];
        check($join['name'] === 'newsletter__newsletter_has_group' && $join['joinColumns'][0]['name'] === 'newsletter_id' && $join['inverseJoinColumns'][0]['name'] === 'group_id' && $join['joinColumns'][0]['onDelete'] === 'CASCADE' && $join['inverseJoinColumns'][0]['onDelete'] === 'CASCADE', 'group join table and CASCADE columns');
        check($c->associationMappings['media']['targetEntity'] === 'WebEtDesign\\MediaBundle\\Entity\\Media', 'Media relation');
        check($c->associationMappings['newsletter']['inversedBy'] === 'contents' && $c->associationMappings['newsletter']['joinColumns'][0]['nullable'] === false, 'owning newsletter relation non-nullable');
        check($c->associationMappings['translations']['targetEntity'] === $t->name && $c->associationMappings['translations']['indexBy'] === 'locale' && $c->associationMappings['translations']['cascade'] === ['persist', 'remove'] && $c->associationMappings['translations']['orphanRemoval'], 'KNP translations indexed by locale with cascade and orphanRemoval');
        check($t->associationMappings['translatable']['targetEntity'] === $c->name && $t->associationMappings['translatable']['joinColumns'][0]['onDelete'] === 'CASCADE' && $t->fieldMappings['locale']['length'] === 5, 'KNP inverse translation relation and locale');
        check($t->table['uniqueConstraints']['newsletter__content_translation_unique_translation']['columns'] === ['translatable_id', 'locale'], 'KNP unique translation constraint');
        check($l->associationMappings['user']['targetEntity'] === 'App\\Entity\\User\\User', 'application User relation');
        $gedmo = $annotations ? new Gedmo\Timestampable\Mapping\Driver\Annotation() : new Gedmo\Timestampable\Mapping\Driver\Attribute();
        $gedmo->setAnnotationReader($annotations ? new Doctrine\Common\Annotations\AnnotationReader() : new Gedmo\Mapping\Driver\AttributeReader());
        $timestampable = [];
        $gedmo->readExtendedMetadata($l, $timestampable);
        check($timestampable === ['create' => ['createdAt'], 'update' => ['updatedAt']], 'Gedmo timestampable create/update metadata');
        $actual = [];
        foreach ($metadata as $short => $meta) {
            $actual[$short] = snapshot($meta);
        }
        $actual['gedmo'] = canonical($timestampable);
        $actual = canonical($actual);
        if ($capturing) {
            if (file_exists($baselinePath)) {
                throw new RuntimeException('Refusing to overwrite baseline: ' . $baselinePath);
            }
            file_put_contents($baselinePath, json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
            echo "SNAPSHOT $baselinePath\n";
        } else {
            $expected = canonical(json_decode(file_get_contents($baselinePath), true, 512, JSON_THROW_ON_ERROR));
            foreach ($actual as $short => $mapping) {
                check(isset($expected[$short]) && $expected[$short] === $mapping, "$short complete metadata identical to annotation baseline");
            }
            check(array_keys($actual) === array_keys($expected), 'baseline covers exactly the same entities and Gedmo metadata');
        }
    }
} catch (Throwable $error) {
    check(false, get_class($error) . ': ' . $error->getMessage());
}
echo "RESULT passed=$passed failed=$failed\n";
exit($failed === 0 ? 0 : 1);
