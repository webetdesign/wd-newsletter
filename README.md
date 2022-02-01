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
  mailer: 'swiftmailer.mailer.spool_mailer'
  routes:
    home: fr_home
  class:
    media: App\Entity\Media
    document: App\Entity\Document # Pas obligatoire (Le repository doit implémenter ContentCollectionRepositoryInterface)
    actuality: App\Entity\News # Pas obligatoire (Le repository doit implémenter ContentCollectionRepositoryInterface)
  noreply: 'noreply@your-site.fr'
  locales: [fr, en]
  models:
    defaut:
      name: 'name'
      sender: 'sender'
      email: 'email'
      template: 'newsletters/default.html.twig'
      txt: 'newsletters/default.txt.twig'
      contents:
        - {code: 'main_color', label: 'Couleur principale', type: COLOR}
        - {code: 'main_picture', label: 'Image de la newsletter', type: MEDIA}
        - {code: 'picture_margin', label: "Marge de l'image", help: "Marge de l'image à gauche et à droite" ,type: TEXT}        
        - {code: 'title', label: 'Titre',type: WYSYWYG}
```
4° Add token to User

```php
<?php 

use WebEtDesign\NewsletterBundle\Entity\NewsletterTrait;

class User {

    use NewsletterTrait;

}
```
5° Add routes
````yaml
#config/packages/routes.yaml

newsletter.routes:
  resource: "@WDNewsletterBundle/Resources/config/routing.yaml"

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

7° add sonata_media config
```yaml
# config/packages/sonata_media.yaml
sonata_media:
  contexts:
    newsletter:
      providers:
        - sonata.media.provider.image
      formats:
        small: { width: 100 , quality: 70}
        big:   { width: 900 , quality: 70}
```

8° swiftmailer configuration
```yaml
#config/packages/swiftmailer.yaml
swiftmailer:
    default_mailer: direct_mailer
    mailers:
        direct_mailer:
            url: '%env(MAILER_URL)%'
        spool_mailer:
            url: '%env(MAILER_URL)%'
            spool:
                type: 'file'
                path: '%kernel.project_dir%/var/spool'

```

## Models : 

#### Paramèters

- name : title of the model 
- sender : name of the people who send the newsletter
- email : email to reply 
- template : html of template of the model.
- txt : txt of template of the model.
- contents :
    - code : use to find and render the content
    - label : name display in the admin
    - type : on of them --> [COLOR, MEDIA, WYSYWYG, TEXT, ACTUALITIES, DOCUMENTS]

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


## Note :

When you subscribe to the newsletter, don't forget to remove it from the unsub list 
