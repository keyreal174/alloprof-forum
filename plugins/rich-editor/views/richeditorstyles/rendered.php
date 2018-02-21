<?php

$operations = '
[{"insert":"Normal\n"},{"attributes":{"code":true},"insert":"Code Inline"},{"insert":"\n"},{"attributes":{"bold":true},"insert":"Bold"},{"insert":"\n"},{"attributes":{"italic":true},"insert":"italic"},{"insert":"\n"},{"attributes":{"italic":true,"bold":true},"insert":"bold italic"},{"insert":"\n"},{"attributes":{"strike":true,"italic":true,"bold":true},"insert":"bold italic strike"},{"insert":"\n"},{"attributes":{"strike":true,"italic":true,"bold":true,"link":"http://test.com"},"insert":"bold italic strike link"},{"insert":"\nTitle"},{"attributes":{"header":1},"insert":"\n"},{"insert":"Subtitle"},{"attributes":{"header":2},"insert":"\n"},{"insert":"Quote"},{"attributes":{"blockquote":true},"insert":"\n"},{"insert":"CodeBlock"},{"attributes":{"code-block":true},"insert":"\n"},{"insert":"Spoiler"},{"attributes":{"spoiler":true},"insert":"\n"},{"insert":"\nImage Embed"},{"attributes":{"header":2},"insert":"\n"},{"insert":{"image-embed":{"url":"https://images.pexels.com/photos/31459/pexels-photo.jpg?w=1260&h=750&dpr=2&auto=compress&cs=tinysrgb","alt":"Some Alt Text"}}},{"insert":"Video Embed"},{"attributes":{"header":2},"insert":"\n"},{"insert":{"video-placeholder":{"photoUrl":"https://i.ytimg.com/vi/wupToqz1e2g/hqdefault.jpg","url":"https://www.youtube.com/embed/wupToqz1e2g","name":"Video Title","width":1858,"height":1276,"simplifiedRatio":{"numerator":638,"denominator":929,"shorthand":"929:638"}}}},{"insert":"Internal Link Embed"},{"attributes":{"header":2},"insert":"\n"},{"insert":{"link-embed":{"url":"https://www.google.ca/","userPhoto":"https://secure.gravatar.com/avatar/b0420af06d6fecc16fc88a88cbea8218/","userName":"steve_captain_rogers","timestamp":"2017-02-17 11:13","humanTime":"Feb 17, 2017 11:13 AM","excerpt":"The Battle of New York, locally known as \"The Incident\", was a major battle between the Avengers and Loki with his borrowed Chitauri army in Manhattan, New York City. It was, according to Loki\'s plan, the first battle in Loki\'s war to subjugate Earth, but the actions of the Avengers neutralized the threat of the Chitauri before they could continue the invasion."}}},{"insert":"External Link Embed With Image"},{"attributes":{"header":2},"insert":"\n"},{"insert":{"link-embed":{"url":"https://www.google.ca/","name":"Hulk attacks New York, kills 17, injures 23 in deadliest attack in 5 years   Hulk attacks New York, kills 17, injures 23 in deadliest attack in 5 years","source":"nytimes.com","linkImage":"https://cdn.mdn.mozilla.net/static/img/opengraph-logo.72382e605ce3.png","excerpt":"The Battle of New York, locally known as \"The Incident\", was a major battle between the Avengers and Loki with his borrowed Chitauri army in Manhattan, New York City. It was, according to Loki\'s plan, the first battle in Loki\'s war to subjugate Earth, but the actions of the Avengers neutralized the threat of the Chitauri before they could continue the invasion."}}},{"insert":"External Link Embed "},{"attributes":{"header":2},"insert":"\n"},{"insert":{"link-embed":{"url":"https://www.google.ca/","name":"Hulk attacks New York, kills 17, injures 23 in deadliest attack in 5 years   Hulk attacks New York, kills 17, injures 23 in deadliest attack in 5 years","source":"nytimes.com","excerpt":"The Battle of New York, locally known as \"The Incident\", was a major battle between the Avengers and Loki with his borrowed Chitauri army in Manhattan, New York City. It was, according to Loki\'s plan, the first battle in Loki\'s war to subjugate Earth, but the actions of the Avengers neutralized the threat of the Chitauri before they could continue the invasion."}}},{"insert":"\n\n"}]
';

echo "<div class='Item-Body'><div class='Message'>";
echo Gdn_Format::rich($operations);
echo "</div></div>";
