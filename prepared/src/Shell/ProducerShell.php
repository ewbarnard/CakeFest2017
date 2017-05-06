<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 12:20 PM
 */

namespace App\Shell;

use App\DemoLogger\DemoLoggerUtil;
use Cake\Console\Shell;

class ProducerShell extends Shell {
    public function main() {
        DemoLoggerUtil::log('Line One', 'From Line ' . __LINE__);
        DemoLoggerUtil::log('Line Two', 'From Line ' . __LINE__);

        DemoLoggerUtil::finalize();
        $this->verbose('Done.');
    }
}
