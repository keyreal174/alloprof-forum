# Extend DB for Alloprof Forum

### How to add more fields

- **Add fields**
    - Add fields in structure() in class.alloprof.plugin.php
    - DO NOT update the old code.
    - Please add comment before you define fields.
    e.g.

            /* Add *** feature */

            Gdn::structure->table('Example')
                    ->column('Test', 'int', true)
                    ->set()

            /* Add *** feature */

            Gdn::structure->table('Example')
                    ->column('Test', 'int', true)
                    ->set()
    - If you are going to update a field which is already published, then please don't make any conflict with new field.
    It would be better to check if the new field is already exist.
    e.g.

            /* Check if ReactionType table is already exist. */

            $St = Gdn::structure();
            $St->table('ReactionType');
            $ReactionTypeExists = $St->tableExists();

- **Enable plugin**
    - Please go to plugin list page.
    - Disable alloprof plugin and enable again.