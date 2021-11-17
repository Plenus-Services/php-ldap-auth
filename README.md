# PHP LDAP authentication

Install:

<code>composer require plenusservices/php-ldap-auth</code>

Use:

```php
/*
|--------------------------------------------------------------------------
| Register The Auto Loader Composer
|--------------------------------------------------------------------------
|
| (EN) Class loader using composer for the entire application
| (ES) Cargador de clases mediante composer para toda la aplicacion
|
*/

(file_exists(__DIR__ . '/../vendor/autoload.php')) ? require __DIR__ . '../vendor/autoload.php': die("🐞");

use Plenusservices\PhpLdap\Conection;


// config
$ldapserver = 'svr.domain.com';
$ldapuser   = 'administrator';
$ldappass   = 'PASSWORD_HERE';
$ldapgroup  = 'ENGINEERING';
$ldaptree   = "OU=SBSUsers,OU=Users,OU=MyBusiness,DC=myDomain,DC=local";
$app        = new Conection($ldapuser, $ldappass, $ldapserver, $ldapgroup, fn () => true);
$filter2    = "(&(samaccountname={$ldapuser}*)(memberOf={$ldapgroup}))";
$justthese  = ["ou", "sn", "givenname", "mail"];

//Only authentication
if ($app->Auth($ldaptree, $filter2)): 
    //successful authentication
endif;

//authentication and get user data 
$result = $app->Auth($ldaptree, $filter2, true, $justthese);
print_r($result);

```

