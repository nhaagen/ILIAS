<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Wizard\StaticSequence;

use \ILIAS\UI\Component\Input\Container\Wizard;

/**
 *
 */
function staticSequence()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();
    $url = str_replace('&reset=1', '', $DIC->http()->request()->getRequestTarget());
     
    //super minimalistic session storage
    $storage = new class() implements Wizard\Storage {
        protected $k = '_wizard_staticseq_data';
        public function set(mixed $data) : void
        {
            $data = array_merge($this->get(), array_shift($data));
            $_SESSION[$this->k] = $data;
        }
        public function get() : mixed
        {
            if (!array_key_exists($this->k, $_SESSION)) {
                $this->set([]);
            }
            return $_SESSION[$this->k];
        }
        public function reset($data)
        {
            $_SESSION[$this->k] = $data;
        }
    };

    $steps = [];
    $steps[] = function ($factory, $data) use ($refinery) {
        $inputs = [
            $factory->fields()->text('What is your name?')
                ->withValue($data['name'])
                ->withAdditionalTransformation(
                    $refinery->custom()->transformation(fn ($v) => ['name' => $v])
                )
        ];
        return $factory->step($inputs, 'Step 1', 'Tell us something about yourself');
    };

    $steps[] = function ($factory, $data) use ($refinery) {
        $inputs = [
            $factory->fields()->numeric('How old are you?')
                ->withValue($data['age']),
            $factory->fields()->numeric('And how old dow you feel?')
                ->withValue($data['felt_age'])
        ];
        return $factory->step($inputs, 'Step 2', 'Tell us more about yourself')
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    fn ($v) => [[
                        'age' => $v[0],
                        'felt_age' => $v[1]
                    ]]
                )
            );
    };

    $steps[] = function ($factory, $data) use ($refinery) {
        $inputs = [
            $factory->fields()->textarea('comments')
                ->withValue($data['comments'])
                ->withAdditionalTransformation(
                    $refinery->custom()->transformation(fn ($v) => ['comments' => $v])
                )
        ];
        return $factory->step($inputs, 'Step 3', 'anything else?');
    };

    //build wizard
    $wizard = $f->input()->container()->wizard()->staticsequence(
        $storage,
        $steps,
        $url,
        'Collect Something',
        'we collect some user input...'
    );

    
    // init/reset
    if (!isset($_SESSION['_wizard_staticseq_data']) || $_GET['reset'] == 1) {
        $storage->reset([
            'name' => 'name?',
            'age' => 0,
            'felt_age' => 0,
            'comments' => ''
        ]);
    }

    // run wizard
    if ($request->getMethod() == "POST") {
        $wizard = $wizard->withRequest($request);
    }
    if ($wizard->isFinished()) {
        $out = 'Yay - done it!';
    } else {
        $out = $r->render($wizard);
    }

    return "<pre>"
        . print_r($wizard->getStoredData(), true)
        . $r->render($f->button()->shy('click here to reset storage', $url . '&reset=1'))
        . "</pre><br/>"
        . "</pre><br />"
        . $out;
}
