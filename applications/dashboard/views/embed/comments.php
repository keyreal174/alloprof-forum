<h1><?php echo t('Universal Comments Embed Code'); ?></h1>
    <?php
    echo t('AboutCommentEmbedding', "Vanilla can be used as a drop-in replacement for your blog's native commenting system. As a matter of fact, it can be used to add comments to any page on the web.");
    ?>
        <p>You can use Vanilla as a commenting system for your website, and all
            contributed comments will also be present in your discussion forum. Vanilla
            Comments can be used on any website using the following code.</p>

        <p class="AlertMessage"><strong>Note:</strong> You MUST define the <code>vanilla_forum_url</code>
            and <code>vanilla_identifier</code> settings before pasting this script into
            your web page.</p>

        <pre class="CopyBox">&lt;div id="vanilla-comments">&lt;/div>
&lt;script type="text/javascript">
    <strong>/*** Required Settings: Edit BEFORE pasting into your web page ***/
        var vanilla_forum_url = '<?php echo url('/', true); ?>'; // The full http url & path to your vanilla forum
        var vanilla_identifier = 'your-content-identifier'; // Your unique identifier for the content being commented on</strong>

    /*** Optional Settings: Ignore if you like ***/
    // var vanilla_discussion_id = ''; // Attach this page of comments to a specific Vanilla DiscussionID.
    // var vanilla_category_id = ''; // Create this discussion in a specific Vanilla CategoryID.

    /*** DON'T EDIT BELOW THIS LINE ***/
    (function() {
        var vanilla = document.createElement('script');
        vanilla.type = 'text/javascript';
        var timestamp = new Date().getTime();
        vanilla.src = vanilla_forum_url + '/js/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(vanilla);
    })();
&lt;/script>
&lt;noscript>Please enable JavaScript to view the &lt;a href="http://vanillaforums.com/?ref_noscript">comments powered by Vanilla.&lt;/a>&lt;/noscript>
&lt;div class="vanilla-credit">
    &lt;a class="vanilla-anchor" href="http://vanillaforums.com">Comments by &lt;span class="vanilla-logo">Vanilla&lt;/span>&lt;/a>
&lt;/div>
</pre>
        <p class="WarningMessage">&uarr; Copy and paste this code into the web page where you want the comments to
            appear.</p>

        <p>&nbsp;</p>

        <h2>Comment Counts</h2>

        <p>To show the number of comments on each blog post on your main blog page, use the following code.</p>

        <p class="AlertMessage"><strong>Note:</strong> You MUST define the <code>vanilla_forum_url</code>
            before pasting this script into your web page.</p>

        <pre class="CopyBox">&lt;script type="text/javascript">
    <strong>/*** Required Settings: Edit BEFORE pasting into your web page ***/
        var vanilla_forum_url = '<?php echo url('/', true); ?>'; // The full http url & path to your vanilla forum</strong>

    /*** Optional Settings: customize the format of the comment counts. Html is allowed. */
    // var vanilla_comments_none = 'No Comments';
    // var vanilla_comments_singular = '1 Comment';
    // var vanilla_comments_plural = '[num] Comments';

    /*** DON'T EDIT BELOW THIS LINE ***/
    (function() {
        var vanilla_count = document.createElement('script');
        vanilla_count.type = 'text/javascript';
        vanilla_count.src = vanilla_forum_url + '/js/count.js';
        (document.getElementsByTagName('head')[0] ||
        document.getElementsByTagName('body')[0]).appendChild(vanilla_count);
    })();
&lt;/script>
        </pre>
        <p class="WarningMessage">&uarr; Copy &amp; paste this code at the bottom of the page right before the closing
            &lt;/body> tag.</p>

        <p>&nbsp;</p>

        <p><strong>One more thing!</strong></p>

        <p>You need to tell Vanilla where the comment counts are located in your page. To achieve this, add a <strong>vanilla-identifier</strong>
            attribute to the anchor linking to the comments. The vanilla-identifier is the same value used above when
            embedding the comments into the page.</p>

        <pre class="CopyBox">&lt;a href="http://yourdomain.com/path/to/page/with/comments/#vanilla_comments" <strong>vanilla-identifier="embed-test"</strong>>Comments&lt;/a>
        </pre>

        <p>Vanilla will then replace the content of the anchor (in this case, the word "Comments") with the number of
            comments on the page in question.</p>
