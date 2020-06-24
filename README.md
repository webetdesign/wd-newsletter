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
          class:
            media: App\Entity\Media
          noreply: 'noreply@your-site.fr'
          locales: [fr, en]
          models:
            defaut:
              title: 'title'
              name: 'defaut'
              sender: 'sender'
              email: 'email'
              template: 'newsletters/defaut.html.twig'
              txt: 'newsletters/defaut.txt.twig'
              contents:
                - {code: 'main_color', label: 'Couleur principale', type: COLOR}
                - {code: 'main_picture', label: 'Image de la newsletter', type: MEDIA}
                - {code: 'picture_margin', label: "Marge de l'image", help: "Marge de l'image à gauche et à droite" ,type: TEXT}        
                - {code: 'title', label: 'Titre',type: WYSYWYG}
```

## Models : 

#### Paramèters

- title : title of the email 
- name : name of the model
- sender : name of the people who send the newsletter
- email : email who send the newsletter
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
