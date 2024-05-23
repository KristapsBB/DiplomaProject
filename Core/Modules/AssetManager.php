<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Core;

class AssetManager
{
    private const ASSET_TYPE_STYLE = 'style';
    private const ASSET_TYPE_SCRIPT = 'script';

    /**
     * the path from the application root to the asset directory
     */
    private string $path_to_assets = 'assets/';
    private string $url_to_assets = 'assets/';
    private array $assets_list = [];
    private array $enqueued_assets = [];

    public function configure(array $config_params)
    {
        $this->setPathToAssets($config_params['path_to_assets']);
    }

    /**
     * sets the path from the application root to the asset directory
     */
    public function setPathToAssets(string $path_to_assets = 'assets')
    {
        $this->path_to_assets = $path_to_assets . '/';
    }

    /**
     * returns the path from the application root to the asset directory
     */
    public function getPathToAssets(): string
    {
        return $this->path_to_assets;
    }

    public function getUrlToAssets(): string
    {
        return $this->url_to_assets;
    }

    public function generatePathToAsset(string $asset_uri): string
    {
        $path_to_asset_file = Core::getCurrentApp()->getAppRoot();
        $path_to_asset_file .= $this->getPathToAssets();
        $path_to_asset_file .= $asset_uri;

        return $path_to_asset_file;
    }

    /**
     * returns true if the asset file exists
     */
    public function existsAsset(string $asset_file_uri): bool
    {
        return file_exists($this->generatePathToAsset($asset_file_uri));
    }

    public function registerStyle(string $style_name, string $uri, string $version = '')
    {
        $this->registerAsset(self::ASSET_TYPE_STYLE, $style_name, $uri, $version);
    }

    public function registerScript(string $script_name, string $uri, string $version = '', bool $is_module = false)
    {
        $params = [];

        if ($is_module) {
            $params = [
                'is_module' => $is_module,
            ];
        }

        $this->registerAsset(self::ASSET_TYPE_SCRIPT, $script_name, $uri, $version, $params);
    }

    private function registerAsset(
        string $asset_type,
        string $asset_name,
        string $uri,
        string $version = '',
        ?array $params = []
    ) {
        if (!$this->existsAsset($uri)) {
            throw new \Exception($asset_type . ' file not found: ' . $uri);
        }

        $this->assets_list[$asset_type][$asset_name] = [
            'uri' => $uri,
            'version' => $version,
            'params' => $params,
        ];
    }

    public function enqueueStyle(string $style_name)
    {
        $this->enqueueAsset(self::ASSET_TYPE_STYLE, $style_name);
    }

    public function enqueueStyles(array $style_names)
    {
        $this->enqueueAssets(self::ASSET_TYPE_STYLE, $style_names);
    }

    public function enqueueScript(string $script_name)
    {
        $this->enqueueAsset(self::ASSET_TYPE_SCRIPT, $script_name);
    }

    public function enqueueScripts(array $script_names)
    {
        $this->enqueueAssets(self::ASSET_TYPE_SCRIPT, $script_names);
    }

    public function dequeueStyle(string $style_name)
    {
        $this->dequeueAsset(self::ASSET_TYPE_STYLE, $style_name);
    }

    public function dequeueScript(string $script_name)
    {
        $this->dequeueAsset(self::ASSET_TYPE_STYLE, $script_name);
    }

    private function enqueueAsset(string $asset_type, string $asset_name)
    {
        if (
            !array_key_exists($asset_type, $this->assets_list)
            || !array_key_exists($asset_name, $this->assets_list[$asset_type])
        ) {
            throw new \Exception("The $asset_type file '$asset_name' is not registered");
        }

        if (!array_key_exists($asset_type, $this->enqueued_assets)) {
            $this->enqueued_assets[$asset_type] = [];
        }

        $this->enqueued_assets[$asset_type][] = [
            'asset_name' => $asset_name,
        ];
    }

    private function enqueueAssets(string $asset_type, array $asset_names)
    {
        foreach ($asset_names as $asset_name) {
            $this->enqueueAsset($asset_type, $asset_name);
        }
    }

    private function dequeueAsset(string $asset_type, string $asset_name)
    {
        if (!array_key_exists($asset_type, $this->assets_list)) {
            return;
        }

        $key = array_search($asset_name, $this->assets_list[$asset_type]);

        if (false === $key) {
            return;
        }

        unset($this->assets_list[$asset_type][$key]);
    }

    public function printStyle(string $url)
    {
        echo '<link rel="stylesheet" href="' . $url . '">';
    }

    public function printScript(string $url, bool $is_module = false)
    {
        $html = '<script src="' . $url . '" ';

        if ($is_module) {
            $html .= 'type="module"';
        }

        $html .= ' ></script>';

        echo $html;
    }

    public function printEnqueuedStyles()
    {
        $this->walkEnqueuedAssets(self::ASSET_TYPE_STYLE, 'printStyle');
    }

    public function printEnqueuedScripts()
    {
        $this->walkEnqueuedAssets(self::ASSET_TYPE_SCRIPT, 'printScript');
    }

    /**
     * @param string $method_name the name of the method of this class
     */
    private function walkEnqueuedAssets(string $asset_type, string $method_name)
    {
        if (!array_key_exists($asset_type, $this->enqueued_assets)) {
            return;
        }

        foreach ($this->enqueued_assets[$asset_type] as $asset_data) {
            $asset_name = $asset_data['asset_name'];

            if (!isset($this->assets_list[$asset_type][$asset_name])) {
                continue;
            }

            $asset   = $this->assets_list[$asset_type][$asset_name];
            $version = $asset['version'] ?? null;
            $params  = $asset['params'] ?? [];

            $asset_url = $this->generateAssetUrl($asset['uri'], $version);

            $this->{$method_name}($asset_url, ...$params);
        }
    }

    public function generateAssetUrl(string $asset_uri, string $version = ''): string
    {
        $http = Core::getCurrentApp()->getHttp();

        $asset_url = $http->generateUrl(
            '/' . $this->getUrlToAssets() . $asset_uri,
            !empty($version) ? ['version' => $version] : null
        );

        return $asset_url;
    }
}
