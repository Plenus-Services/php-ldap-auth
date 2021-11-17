<?php

namespace Plenusservices\PhpLdap;

use Exception;
use Throwable;

class Conection
{
    /**
     * @var string 
     * @access public
     */
    public $extensionName = "ldap";

    /**
     * @var array 
     * @access public
     */
    private $tmp;

    /**
     * @var bool
     * @access private
     */
    private $tmpSession;

    /**
     * @var object
     * @access public
     */
    public $session;

    /**
     * @var string 
     * @access private
     */
    private $hostName;

    /**
     * @var array 
     */
    public $result;


    /**
     * @param string $userName
     * (ES) Cuenta de usuario del directorio activo (EN) User account active directory
     * @param string $password;
     * @param string $domain;
     * (ES) Nombre de dominio (EN) Domain name
     * @param string $group;
     * (ES) Grupo al que pertence (EN) Member of
     * @param function $callback
     * @param int $port
     * (ES) Numero de puerto del protocolo LDAP
     * @param  int $ldapVersion
     * (ES) Versión del protocolo de conexión LDAP
     */
    public function __construct(public string $userName, public string $password, public string $domain, public string $group, public $callback = false, public int|string $port = 389, public int|string $ldapVersion = 3)
    {
        //validar extensión y funciones
        $this->Validate();
        $this->userName = "{$this->userName}@{$this->domain}";
        $this->hostName = "ldap://{$this->domain}";
        $this->session = ldap_connect($this->hostName, $this->port);
    }

    public function Validate(): void
    {
        $this->LdapIsset();
    }
    public function LdapIsset(): void
    {
        try {
            $this->issetLdapExtension();
            foreach ($this->RequiredsFunctions() as $key => $value) (function_exists($value)) ? true : throw new Exception("Falta la función {$value}", 1);
        } catch (\Exception $th) {
            throw $th->getMessage();
        }
    }
    public function issetLdapExtension(): void
    {
        $this->tmp = get_loaded_extensions();
        if (!in_array($this->extensionName,  $this->tmp)) :
            throw new Exception("Falta la extensión {$this->extensionName}", 1);
        endif;
    }
    public function RequiredsFunctions(): array
    {
        return   ['ldap_connect', 'ldap_set_option', 'ldap_search', 'ldap_get_entries', 'ldap_unbind', 'ldap_bind'];
    }

    public function Start(): bool
    {
        try {
            ldap_set_option($this->session, LDAP_OPT_PROTOCOL_VERSION, $this->ldapVersion);
            ldap_set_option($this->session, LDAP_OPT_REFERRALS, 0);
            return (@ldap_bind($this->session, $this->userName, $this->password)) ? true : false;
        } catch (Throwable $th) {
            return false;
        }
    }

    /**
     *  @param array|string $filter
     * (EN) The search filter can be simple or advanced, using boolean operators in the format described in the LDAP 
     * documentation (see the Netscape Directory SDK for full information on filters)
     * https://wiki.mozilla.org/Mozilla_LDAP_SDK_Programmer%27s_Guide/Searching_the_Directory_With_LDAP_C_SDK.
     * 
     * (ES) ...
     * @param string $search_filter
     * @param bool $getData
     * 
     */
    public function Auth(array|string $filter, string $search_filter = "", bool $getData = false, array $select = ["*"]): mixed
    {
        $this->tmpSession =  $this->Start();
        if ($getData && $this->tmpSession) {
            $this->result = @ldap_search($this->session,  $filter, $search_filter, $select, 1, -1, -1);
            return ($this->tmpSession ? ($this->result ?  ldap_get_entries($this->session, $this->result) : false) : false);
        }
        return $this->tmpSession;
    }
}
