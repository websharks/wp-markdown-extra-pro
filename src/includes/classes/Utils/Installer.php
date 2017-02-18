<?php
/**
 * Install utils.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\Utils;

use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Interfaces;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Traits;
#
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\AppFacades as a;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WpMarkdownExtra\Pro\Classes\CoreFacades as c;
#
use WebSharks\WpSharks\Core\Classes as SCoreClasses;
use WebSharks\WpSharks\Core\Interfaces as SCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as SCoreTraits;
#
use WebSharks\Core\WpSharksCore\Classes as CoreClasses;
use WebSharks\Core\WpSharksCore\Classes\Core\Base\Exception;
use WebSharks\Core\WpSharksCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpSharksCore\Traits as CoreTraits;
#
use function assert as debug;
use function get_defined_vars as vars;

/**
 * Install utils.
 *
 * @since 170126.30913 Initial release.
 */
class Installer extends SCoreClasses\SCore\Base\Core
{
    /**
     * Other install routines.
     *
     * @since 170126.30913 Initial release.
     *
     * @param array $history Install history.
     */
    public function onOtherInstallRoutines(array $history)
    {
        // Do something here.
        // $this->installSomething();
        // i.e., Create protected methods in this class.
    }

    /**
     * Version-specific upgrades.
     *
     * @since 170126.30913 Initial release.
     *
     * @param array $history Install history.
     */
    public function onVsUpgrades(array $history)
    {
        // Do something here.
        // VS upgrades run 'before' any other installer.
        // if (version_compare($history['last_version'], '000000', '<')) {
        //     $this->App->Utils->VsUpgrades->fromLt000000();
        // }
    }
}
