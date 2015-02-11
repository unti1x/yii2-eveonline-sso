# Yii2 EveOnline SSO OAuth2 client extension

This extension uses standart `yii2-authclient` to allow auth via EveOnline site.

## Dependencies

The only required package is `yiisoft/yii2-authclient`

## Installation

### Via Composer

Add line `"unti1x/yii2-eveonline-sso": "*"` in your `composer.json` into `require`-section 
and then update packages or use following command:

```bash
composer require "unti1x/yii2-eveonline-sso" "*"
```

### Manual

Download and unpack wherever you want. Note that `yiisoft/yii2-authclient` also required.

## Usage

At first add extension as authclient into your web config:

```php
    'components' => [

        // ...

        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [

                // ...

                'eve-online-sso' => [
                    'class' => 'yii\authclient\clients\EveOnlineSSO',
                    'clientId' => 'Change to your client ID',
                    'clientSecret' => 'Change to your client secret',
                ],

                // ...

            ],
        ]

        // ...

    ]

```

You can also add some fields into `User` model, like `character_id`, `character_name`, `owner_hash`.
The last one is strongly recomended because characters can be transfered to another account and CCP
provides a way to check this with unique code (see SSO manual, "Obtain the character ID" section). 


Next register `auth` action in a controller:

```php
    public function actions()
    {
        return [

            // ...

            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],

            // ...

        ];
    }
```

And implement `successCallback`-method that should be called on user successfully authorized.
Check Yii2 docs for more information.

You can use something like this:

```php

    public function successCallback($client)
    {
        $attributes = $client->getUserAttributes();
        
        if(Yii::$app->user->isGuest) {
            // trying to find existing user by owner hash
            $user = User::findOne(['owner_hash' => $attributes['CharacterOwnerHash']]);
            
            if($user) {
                Yii::$app->user->login($user);
                
                return $this->redirect('index');
            }
            
            // creating new user if nothing found
            $user = new User();
            $user->attributes = [
                  'character_id' => $attributes['CharacterID']
                , 'character_name' => $attributes['CharacterName']
                , 'owner_hash' => $attributes['CharacterOwnerHash']
            ];
            $user->save();
            
            Yii::$app->user->login($user);
        }
        
        return $this->redirect('index');
    }

```

Example code for views:

Twig
```html
{{ use('yii/authclient/widgets/AuthChoice') }}

{% if app.user.isGuest %}
    <a href="{{ path('site/auth', {'authclient': 'eve-online-sso'}) }}">
        <img src="https://images.contentful.com/idjq7aai9ylm/18BxKSXCymyqY4QKo8KwKe/c2bdded6118472dd587c8107f24104d7/EVE_SSO_Login_Buttons_Small_White.png?w=195&h=30" alt="SSO auth" />
    </a>
{% else %}
    <div class="user-avatar">
        <img src="//image.eveonline.com/Character/{{ app.user.identity.character_id }}_128.jpg" alt="avatar" />
    </div>
    {{ app.user.identity.character_name }}
{% endif %}

```

PHP
```php
<?php 
    use yii\authclient\widgets\AuthChoice;
    use yii\helpers\Url;
?>

<?php if(Yii::$app->user->isGuest): ?>
    <a href="<?= Url::toRoute('site/auth', ['authclient' => 'eve-online-sso']) ?>">
        <img src="https://images.contentful.com/idjq7aai9ylm/18BxKSXCymyqY4QKo8KwKe/c2bdded6118472dd587c8107f24104d7/EVE_SSO_Login_Buttons_Small_White.png?w=195&h=30" alt="SSO auth" />
    </a>
<?php else:?>
    <div class="user-avatar">
        <img src="//image.eveonline.com/Character/<?= Yii::$app->user->identity->character_id ?>_128.jpg" alt="avatar" />
    </div>
    <?= Yii::$app->user->identity->character_name ?>
<?php endif; ?>

```

## Using API
Since private CREST not released yet, the only available method is `verify`.

## Links

 * [Yii2 AuthClient documentation](http://www.yiiframework.com/doc-2.0/ext-authclient-index.html)
 * [EveOnline Single Sign-On manual](https://developers.eveonline.com/resource/single-sign-on)
 * [EveOnline image server](http://image.eveonline.com/)

## License

CreativeCommons Attribution-ShareAlike 4.0 
 ([user friendly](https://creativecommons.org/licenses/by-sa/4.0/), [legal](https://creativecommons.org/licenses/by-sa/4.0/legalcode))
