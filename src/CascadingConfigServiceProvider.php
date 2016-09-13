<?php

namespace bryceray1121\CascadingConfig;

use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use SplFileInfo as SysSplFileInfo;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Foundation\Application;

class CascadingConfigServiceProvider extends LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        parent::bootstrap($app);
        $this->mergeEnvironmentConfigs($app);
    }

    protected function mergeEnvironmentConfigs(Application $app)
    {
        $env = $app->environment();
        $path = config_path().".$env";

        $envConfigPath = (new SysSplFileInfo($path))->getRealPath();

        if (!file_exists($envConfigPath) ||  !is_dir($envConfigPath)) {
            // Nothing to do here
            return;
        }

        $config = $app->make('config');

        foreach (Finder::create()->files()->name('*.php')->in($envConfigPath) as $file) {
            // Run through all PHP files in the current environment's config directory.
            // With each file, check if there's a current config key with the name.
            // If there's not, initialize it as an empty array.
            // Then, use array_replace_recursive() to merge the environment config values
            // into the base values.

            $keyName = $this->getConfigurationNesting($file, $envConfigPath).basename($file->getRealPath(), '.php');

            $oldValues = $config->get($keyName) ?: [];
            $newValues = require $file->getRealPath();

            // Replace any matching values in the old config with the new ones.
            $config->set($keyName, array_replace_recursive($oldValues, $newValues));
        }
    }
}
