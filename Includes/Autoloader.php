<?php
namespace FourDash\Includes;

class FourDash_Autoloader {
    public function __construct() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($class_name) {
        if (strpos($class_name, 'FourDash\\') !== 0) {
            return;
        }

        $class_name = str_replace('FourDash\\', '', $class_name);
        $file_path = $this->convert_class_to_file($class_name);

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    private function convert_class_to_file($class_name) {
        $class_name = str_replace('_', '-', $class_name);
        $file_name = 'class-' . strtolower($class_name) . '.php';
        return FOURDASH_PLUGIN_DIR . 'Includes/' . $file_name;
    }
}

new FourDash_Autoloader();