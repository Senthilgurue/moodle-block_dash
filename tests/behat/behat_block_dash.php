<?php

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
Behat\Mink\Exception\DriverException as DriverException,
Behat\Mink\Exception\ExpectationException as ExpectationException;

class behat_block_dash extends behat_base {

    /**
     * Turns block editing mode on.
     * @Given I switch block editing mode on
     * @Given I turn block editing mode on
     */
    public function i_turn_block_editing_mode_on() {

        global $CFG;
        if ($CFG->branch >= "400") {

            $this->execute('behat_forms::i_set_the_field_to', [get_string('editmode'), 1]);

            if (!$this->running_javascript()) {
                $this->execute('behat_general::i_click_on', [
                    get_string('setmode', 'core'),
                    'button',
                ]);
            }

        } else {
            $this->execute('behat_general::i_click_on', ['Blocks editing on', 'button']);
        }

    }
}