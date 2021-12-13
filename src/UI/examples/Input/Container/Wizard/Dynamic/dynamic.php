<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Wizard\Dynamic;

use \ILIAS\UI\Component\Input\Container\Wizard as Iface;
use \ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use \ILIAS\Refinery\Factory as RefineryFactory;

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
    

    //define the process to create a new step based on some data
    $magic = new class($url) implements Iface\StepBuilder {
        public function __construct($url)
        {
            $this->url = $url;
        }

        public function build(FieldFactory $field_factory, RefineryFactory $refinery, Iface\Step $step, mixed $data) : Iface\Step
        {
            $inputs = [];
            foreach ($data as $idx => $summand) {
                $inputs[] = $field_factory
                    ->numeric(($idx > 0) ? '+' : '')
                    ->withValue($summand)
                    ->withDisabled(true);
            }
            $inputs[] = $field_factory->numeric('+');

            //sum it up (and remove from post)
            $inputs[] = $field_factory
                ->numeric('sum: ')
                ->withValue(array_sum($data))
                ->withDisabled(true)
                ->withAdditionalTransformation(
                    $refinery->custom()->transformation(function ($v) {
                        return null;
                    })
                );

            $filter_nulls_trafo = $refinery->custom()->transformation(function ($v) {
                return array_filter($v, function ($e) {
                    return is_numeric($e);
                });
            });
            return $step
                ->withPostURL($this->url)
                ->withInputs($inputs)
                ->withAdditionalTransformation($filter_nulls_trafo);
        }
    };

    //define positive outcome
    $completeness = function ($values) {
        return array_sum($values) === 10;
    };

    //build wizard
    $wizard = $f->input()->container()->wizard()->dynamic(
        'Count to Ten',
        'continue adding numbers until the sum totals ten.',
        $completeness,
        $magic
    );

    //we will need some external storage...
    $storage_key = '_wizard_dummy_data';
    if (!array_key_exists($storage_key, $_SESSION) || $_GET['reset'] == 1) {
        $_SESSION[$storage_key] = [3, -1, 4];
    }
    $data = $_SESSION[$storage_key];

    //apply data
    $wizard = $wizard->withData($data);

    //post request to wizard, renew data
    if ($request->getMethod() == "POST") {
        $wizard = $wizard->withRequest($request);
        $data = $wizard->getData();
        $_SESSION[$storage_key] = $data;
    }
    
   
    if ($wizard->isFinished()) {
        $out = 'Yay - great counting!';
    } else {
        $out = $r->render($wizard);
    }

    $reset = $r->render($f->button()->shy('reset', $url . '&reset=1'));
    return "<pre>" . print_r($data, true) . "<br />" . $reset . "</pre><br/>"
        . $out;
}
