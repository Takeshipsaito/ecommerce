<?php 

namespace Hcode;

use Rain\Tpl;

class Page {

    private $tpl;
    private $options = [];
    private $defaults = ["data" => []];

    public function __construct($opts = array())
    {
        $this->options = array_merge($this->defaults, $opts);

        // Caminho absoluto para evitar erros no Linux/XAMPP
        $basePath = "/opt/lampp/htdocs/ecommerce";

        $config = array(
            "tpl_dir"   => $basePath . "/views/",
            "cache_dir" => $basePath . "/views-cache/",
            "debug"     => true 
        );

        // Validação de segurança
        if (!is_dir($config['tpl_dir'])) {
            die("ERRO: Pasta /views/ nao encontrada em: " . $config['tpl_dir']);
        }
        if (!is_writable($config['cache_dir'])) {
            die("ERRO: Pasta /views-cache/ sem permissao de escrita! Rode: sudo chmod -R 777 " . $config['cache_dir']);
        }

        Tpl::configure($config);
        $this->tpl = new Tpl;
        
        $this->setData($this->options["data"]);

        // Carrega o topo
        $this->tpl->draw("header");
    }

    private function setData($data = array()) 
    {
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false) 
    {
        $this->setData($data);
        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct()
    {
        // Carrega o rodapé
        $this->tpl->draw("footer");
    }
}