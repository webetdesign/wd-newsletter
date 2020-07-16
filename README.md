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
    "webetdesign/wd-newsletter": "^1"
  }
}
```

## Configure Bundle : 

1° Update composer

2° Add this lines to config/Bundles.php file : 
```php
    ...
    WebEtDesign\AnalyticsBundle\WDNewsletterBundle::class => ['all' => true],
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
  noreply: 'noreply@your-site.fr'
  locales: [fr, en]
  models:
    defaut:
      title: 'title'
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

class User {


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $newsletterToken;
    
    public function getNewsletterToken(): ?string
    {
        return $this->newsletterToken;
    }

    public function setNewsletterToken(?string $newsletterToken): self
    {
        $this->newsletterToken = $newsletterToken;

        return $this;
    }

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
newsletter:
    toolbar:
      <<: *default_toolbar
    allowedContent: true
    filebrowserUploadMethod: form
    filebrowserBrowseRoute: admin_app_media_ckeditor_browser
    filebrowserImageBrowseRoute: admin_app_media_ckeditor_browser
    # Display images by default when clicking the image dialog browse button
    filebrowserImageBrowseRouteParameters:
      provider: sonata.media.provider.image
      context: cms_page
    # Upload file as image when sending a file from the image dialog
    filebrowserImageUploadRoute: admin_app_media_ckeditor_upload
    filebrowserBrowseRouteType: 0
    filebrowserImageUploadRouteParameters:
      provider: sonata.media.provider.image
      context: cms_page # Optional, to upload in a custom context
      format: big # Optional, media format or original size returned to editor
```

7° add sonata_media config
```yaml
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
    - type : on of them --> [COLOR, MEDIA, WYSYWYG, TEXT]

#### Traductions

- All of contents except MEDIA type will be translated in all of the locales provided in the locales config field.
- If a locale property  exist in user the mail will be sent in the defined locale. Else, it will be sent in french    

## Admin : 

```yaml
#config/packages/sonata_admin.yaml
groups:
    [...]
    newsletter:
        label: Newsletters
        on_top: true
        icon: '<i class="fa fa-send"></i>'
```


## Note :

When you subscribe to the newsletter, don't forget to remove it from the unsub list 
