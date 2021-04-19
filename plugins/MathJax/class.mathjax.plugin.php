<?php

/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 */

/**
 * MathJax Plugin
 *
 * This plugin allows the forum to parse MathJax syntax to support rendering of complex mathematical formulas
 * in discussions and comments.
 *
 * Currently, MathJax version 2.4 is supported.
 *
 * Changes:
 *  1.0     Initial release
 *  1.1     Support previews
 *
 * @author Tim Gunter <tim@vanillaforums.com>
 * @package addons
 */
class MathJaxPlugin extends Gdn_Plugin {

    /**
     * Insert MathJax javascript into discussion pages
     *
     * @param DiscussionController $sender
     */
    public function discussionController_render_before($sender) {

        // Add basic MathJax configuration
        $mathJaxConfig = <<<MATHJAX
        <script type="text/x-mathjax-config">
            MathJax.Hub.Config({
                "HTML-CSS": { linebreaks: { automatic: true } },
                SVG: { linebreaks: { automatic: true } },
                tex2jax: {
                    inlineMath: [ ['$$','$$'], ['\\(','\\)'] ]
                },
                ignoreClass: "CommentHeading"
                preview: "TeX",
            });
        </script>
        MATHJAX;
        $sender->Head->addString($mathJaxConfig);
        $sender->addJsFile("https://polyfill.io/v3/polyfill.min.js?features=es6");
        $sender->addJsFile("https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js");
        $sender->addJsFile("live.js", "plugins/MathJax");
    }

    /**
     * Insert MathJax javascript into discussion pages
     *
     * @param DiscussionsController $sender
     */
    public function discussionsController_render_before($sender) {

        // Add basic MathJax configuration
        $mathJaxConfig = <<<MATHJAX
        <script type="text/x-mathjax-config">
            MathJax.Hub.Config({
                "HTML-CSS": { linebreaks: { automatic: true } },
                SVG: { linebreaks: { automatic: true } },
                tex2jax: {
                    inlineMath: [ ['$$','$$'], ['\\(','\\)'] ]
                },
                ignoreClass: "CommentHeading"
                preview: "TeX",
            });
        </script>
        MATHJAX;
        $sender->Head->addString($mathJaxConfig);
        $sender->addJsFile("https://polyfill.io/v3/polyfill.min.js?features=es6");
        $sender->addJsFile("https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js");
        $sender->addJsFile("live.js", "plugins/MathJax");
    }

    /**
     * Insert MathJax javascript into categories page
     *
     * @param CategoriesController $sender
     */
    public function categoriesController_render_before($sender) {

        // Add basic MathJax configuration
        $mathJaxConfig = <<<MATHJAX
        <script type="text/x-mathjax-config">
            MathJax.Hub.Config({
                "HTML-CSS": { linebreaks: { automatic: true } },
                SVG: { linebreaks: { automatic: true } },
                tex2jax: {
                    inlineMath: [ ['$$','$$'], ['\\(','\\)'] ]
                },
                ignoreClass: "CommentHeading"
                preview: "TeX",
            });
        </script>
        MATHJAX;
        $sender->Head->addString($mathJaxConfig);
        $sender->addJsFile("https://polyfill.io/v3/polyfill.min.js?features=es6");
        $sender->addJsFile("https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js");
        $sender->addJsFile("live.js", "plugins/MathJax");
    }

    /**
     * Insert MathJax javascript into search page
     *
     * @param SearchController $sender
     */
    public function searchController_render_before($sender) {

        // Add basic MathJax configuration
        $mathJaxConfig = <<<MATHJAX
        <script type="text/x-mathjax-config">
            MathJax.Hub.Config({
                "HTML-CSS": { linebreaks: { automatic: true } },
                SVG: { linebreaks: { automatic: true } },
                tex2jax: {
                    inlineMath: [ ['$$','$$'], ['\\(','\\)'] ]
                },
                ignoreClass: "CommentHeading"
                preview: "TeX",
            });
        </script>
        MATHJAX;
        $sender->Head->addString($mathJaxConfig);
        $sender->addJsFile("https://polyfill.io/v3/polyfill.min.js?features=es6");
        $sender->addJsFile("https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js");
        $sender->addJsFile("live.js", "plugins/MathJax");
    }
}
