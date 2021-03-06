
/*
 * MathJax Plugin: live js
 *
 * This javascript ensures that newly added content is immediately "jaxed" when
 * loaded via dom manipulation.
 *
 * @author Tim Gunter <tim@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package addons
 */

jQuery(document).on('CommentAdded', function () {
    MathJax.typesetPromise();
});

jQuery('form').on('PreviewLoaded', function() {
    MathJax.typesetPromise();
});

jQuery(document).on('CommentPagingComplete', function() {
    MathJax.typesetPromise();
})