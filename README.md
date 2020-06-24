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
    WebEtDesign\AnalyticsBundle\WDAdminAnalyticsBundle::class => ['all' => true],
    MediaFigaro\GoogleAnalyticsApi\GoogleAnalyticsApi::class => ['all' => true],
    ...
```
3° Create File wd_admin_analytics.yaml : 
```yaml
        wd_admin_analytics:
              parameters:
                     view_ids: [000000000]
                     view_names: ['name']
                     map_key: your-map-key
                     
        # 000000000 = profile id that you can find in the analytics URL, p000000000 :
        #https://analytics.google.com/analytics/web/?hl=en&pli=1#management/Settings/a222222222w1111111111p000000000/   
```
 map_key use for Countries Chart "your-key" 
         [Get Api Key](https://developers.google.com/maps/documentation/javascript/get-api-key#step-1-get-an-api-key), 
         [Enable Api Key](https://cloud.google.com/maps-platform/#get-started)
         
#### If you don't specify a mapKey the map block will be rendered as chart bar
         
4° Add routes :
```yaml
[config/routes.yaml]

wd_admin_analytics.data_api:
  resource: "@WDAdminAnalyticsBundle/Resources/config/routing.yaml"
```       
