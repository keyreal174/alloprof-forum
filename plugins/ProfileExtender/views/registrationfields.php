<?php if (!defined('APPLICATION')) exit();

if (is_array($sender->RegistrationFields)) {
    foreach ($sender->RegistrationFields as $k => $Field) {
        $Name = $Field['Name'] ?? $k;
        $Options = [];
        if ($Field['FormType'] == 'Dropdown') {
            $values = $Field['Options'];
            $labels = val('OptionsLabels', $Field, $Field['Options']);

            // If the config provides an array of labels nested in the profile field ($Field['OptionsLabels']),
            // combine the arrays to create a drop-down with different values and labels.
            $Options = array_combine($values, $labels);

            if ($Name === 'Grade') {
                array_unshift($Options, t('Grade'));
            }
        }

        if ($Field['FormType'] == 'TextBox' && !empty($Field['Options'])) {
            $Options = $Field['Options'];
        }

        if ($Field['FormType'] == 'CheckBox') {
            echo wrap($sender->Form->{$Field['FormType']}($Name, $Field['Label']), 'li');
        } else {
            if ($Name === 'Grade') {
                echo wrap($sender->Form->{$Field['FormType']}($Name, $Options), 'li', ['class' => 'form-group select', 'placeholder' => t('')]);
            } elseif ($Name === 'DisplayName') {
                echo wrap($sender->Form->{$Field['FormType']}($Name, ['placeholder' => t('DisplayName')]), 'li', []);
            } else {
                echo wrap($sender->Form->label($Field['Label'], $Name).
                    $sender->Form->{$Field['FormType']}($Name, $Options), 'li', ['class' => 'form-group']);
            }
        }
    }
}
