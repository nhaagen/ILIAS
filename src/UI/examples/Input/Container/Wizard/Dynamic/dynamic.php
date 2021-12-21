<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Wizard\Dynamic;

use \ILIAS\UI\Component\Input\Container\Wizard;

/**
 *
 */
function dynamic()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();
    $url = str_replace('&reset=1', '', $DIC->http()->request()->getRequestTarget());
    
   
    $magic = new class() implements Wizard\StepBuilder {

        //When is data complete? As long as this ist false, wizard must continue collecting.
        public function isComplete(mixed $data) : bool
        {
            return is_array($data) && array_sum($data) === 10;
        }

        //define the process to create a new step based on some data
        public function build(Wizard\StepFactory $factory, mixed $data) : Wizard\Step
        {
            $inputs = [];
            foreach ($data as $idx => $summand) {
                $inputs[] = $factory->fields()
                    ->numeric(($idx > 0) ? '+' : '')
                    ->withValue($summand)
                    ->withDisabled(true);
            }
            $inputs[] = $factory->fields()->numeric('+');

            $filter_nulls_trafo = $factory->refinery()->custom()->transformation(
                function ($v) {
                    return array_filter($v, function ($e) {
                        return is_numeric($e);
                    });
                }
            );
            return $factory->step($inputs)
                ->withAdditionalTransformation($filter_nulls_trafo);
        }
    };


    //super minimalistic session storage
    $storage = new class() implements Wizard\Storage {
        protected $k = '_wizard_dummy_data';
        public function set(mixed $data) : void
        {
            $_SESSION[$this->k] = $data;
        }
        public function get() : mixed
        {
            return $_SESSION[$this->k];
        }
    };

    //build wizard
    $wizard = $f->input()->container()->wizard()->dynamic(
        $storage,
        $magic,
        $url,
        'Count to Ten',
        'continue adding numbers until the sum totals ten.'
    );
    

    // init/reset
    if (!isset($_SESSION['_wizard_dummy_data']) || $_GET['reset'] == 1) {
        $storage->set([3, -1, 4]);
    }

    // run wizard
    $wizard = $wizard->withRequest($request);
    if ($wizard->isFinished()) {
        $out = 'Yay - great counting!';
    } else {
        $out = $r->render($wizard);
    }

    return "<pre>"
        . print_r($wizard->getStoredData(), true)
        . "<br />"
        . '= ' . print_r(array_sum($wizard->getStoredData()), true)
        . "<br />"
        . $r->render($f->button()->shy('click here to reset storage', $url . '&reset=1'))
        . "</pre><br/>"
        . $out;
}
