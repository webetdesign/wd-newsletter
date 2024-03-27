## Install the Bundle :
```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/webetdesign/wd-newsletter.git"
        }
    ]
}
```
```json
{
  "require": {
    "webetdesign/wd-newsletter": "^1.0.0"
  }
}
```

## Configure Bundle : 

1° Update composer

2° Add this lines to config/Bundles.php file : 
```php
    ...
    WebEtDesign\NewsletterBundle\WDNewsletterBundle::class => ['all' => true],
    ...
```
3° Create File wd-newsletter.yaml : 
```yaml
wd-newsletter:
  enable_log: true # Pour les statistiques
  routes:
    home: fr_home
  class:
    media: App\Entity\Media
    user: App\Entity\User\User
    document: App\Entity\Document # Pas obligatoire (Le repository doit implémenter ContentCollectionRepositoryInterface)
    actuality: App\Entity\News # Pas obligatoire (Le repository doit implémenter ContentCollectionRepositoryInterface)
  noreply:
    email: 'noreply@your-site.fr'
    name: 'Your site'
  replyTo:
    email: 'contact@your-site.fr' #optional
    name: 'Your site' #optional
  locales: [fr, en]
```
````yaml
#config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: "%env(MESSENGER_TRANSPORT_DSN)%"
        routing:
            'Symfony\Component\Mailer\Messenger\SendEmailMessage': async
````
````dotenv
#.env.local
MESSENGER_TRANSPORT_DSN=doctrine://default
````
4° Add token to User (Used to unsubscribe)

```php
<?php 

use WebEtDesign\NewsletterBundle\Entity\NewsletterFields;

class User {

    use NewsletterFields;

}
```
5° Add routes
````yaml
#config/packages/routes.yaml

newsletter.routes:
  resource: "@WDNewsletterBundle/Resources/config/routing.yaml"

````
````yaml
#config/packages/security.yaml
access_control:
  # bien mettre tout en haut
  - { path: ^/newsletter/track/.*, role: [ IS_AUTHENTICATED_ANONYMOUSLY ] }
````

6° add ckeditor config
```yaml
# config/packages/fos_ck_editor.yaml
fos_ck_editor:
  default_config: default
  configs:
    newsletter:
      toolbar:
        - [ Bold, Italic, Underline, -, Cut, Copy, Paste, PasteText, PasteFromWord, -, Undo, Redo, -, BackgroundColor, TextColor, -, NumberedList, BulletedList, -, Outdent, Indent, -, JustifyLeft, JustifyCenter, JustifyRight, JustifyBlock, -, Blockquote, -, Image, Link, Unlink, Table ]
        - [ Format, Maximize, Source ]
      allowedContent: true
      filebrowserUploadMethod: form
      filebrowserBrowseRoute: admin_webetdesign_media_media_ckeditor_browser
      filebrowserImageBrowseRoute: admin_webetdesign_media_media_ckeditor_browser
      # Display images by default when clicking the image dialog browse button
      filebrowserImageBrowseRouteParameters:
        category: default_cms
      # Upload file as image when sending a file from the image dialog
      filebrowserImageUploadRoute: admin_webetdesign_media_media_ckeditor_upload
      extraPlugins: "collapsibleItem,accordionList"
      filebrowserImageUploadRouteParameters:
        category: default_cms
```

7° Token encryption log
`````dotenv
#.env.local
# Possiblité de générer le token depuis le site https://jwt.io/ en mettant un payload vide
NEWSLETTER_IV='mon-token'
`````

8° Send mails 
````shell
bin/console messenger:consume async
````
## Models : 

#### Paramèters

- name : title of the model 
- sender : name of the people who send the newsletter
- email : email to reply 
- template : html of template of the model.
- txt : txt of template of the model.
- contents :
    - code : use to find and render the content
    - type : like cms type
    - label : name display in the admin

````php
#[AsNewsletterModel(code: self::CODE)]
class DefaultModel extends AbstractModel
{
    public const CODE = 'default';

    protected ?string $name = 'name';
    protected ?string $sender = 'noreply@your-site.com';
    protected ?string $email = 'contact@your-site.com';
    protected ?string $template = 'newsletters/default.html.twig';
    protected ?string $txt = 'newsletters/default.txt.twig';

    public function getBlocks(): iterable
    {
        yield BlockDefinition::new('title', WysiwygBlock::code, 'Titre');
        yield BlockDefinition::new('picture_margin', TextBlock::code, "Marge de l'image");
        yield BlockDefinition::new('main_picture', MediaBlock::code, "Image de la newsletter")->setFormOptions([
            'category' => 'default_cms'
        ]);
        yield BlockDefinition::new('main_color', ColorBlock::code, 'Couleur principale');
        yield BlockDefinition::new('documents', DynamicBlock::code, 'Documents')->setAvailableBlocks([
            BlockDefinition::new('document', EntityBlock::code, 'Document')->setFormOptions([
                'class' => User::class
            ])
        ])
        ;

    }
}
````
#### Traductions

- All of contents except MEDIA type will be translated in all of the locales provided in the locales config field.
- If a locale property  exist in user the mail will be sent in the defined locale. Else, it will be sent in french    

## Admin : 

```yaml
#config/packages/sonata_admin.yaml
sonata_admin:
  dashboard:
    groups:
      newsletter:
        label: Newsletters
        icon: '<i class="fa fa-send"></i>'
```
