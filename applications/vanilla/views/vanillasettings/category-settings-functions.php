<?php
/**
 *
 */

/**
 * @param array $categories
 * @param int $indent
 * @param bool $allowSorting
 */
function writeCategoryTree($categories, $indent = 0, $allowSorting = true) {
    echo "<ol class=\"js-nestable-list nestable-list\">\n";

    foreach ($categories as $category) {
        writeCategoryItem($category, $indent + 1, $allowSorting);
    }
    echo "</ol>\n";
}

/**
 * Returns the HTML for a category filter input box for the dashboard.
 *
 * @param array $options
 * @return string
 */
function categoryFilterBox(array $options = []) {
    $form = new Gdn_Form('');

    $containerSelector = isset($options['containerSelector']) ? $options['containerSelector'] : '.js-category-filter-container';
    $cssClass = isset($options['cssClass']) ? $options['cssClass'] : 'form-control';
    $useSearchInput = isset($options['useSearchInput']) ? $options['useSearchInput'] : true;
    $hideContainerSelector = isset($options['hideContainerSelector']) ? $options['hideContainerSelector'] : '';
    $limit = isset($options['limit']) ? $options['limit'] : 300;
    $parentID = isset($options['parentID']) ? $options['parentID'] : Gdn::controller()->data('ParentID', -1);

    $attr = [
        'class' => 'js-category-filter-input '.$cssClass,
        'placeholder' => t('Search'),
        'data-category-id' => $parentID,
        'data-limit' => $limit,
        'data-container' => $containerSelector
    ];

    if ($hideContainerSelector) {
        $attr['data-hide-container'] = $hideContainerSelector;
    }

    if ($useSearchInput) {
        return $form->searchInput('', '', $attr);
    }

    return $form->input('', '', $attr);
}

/**
 *
 *
 * @param array $category
 * @param int $indent
 * @param bool $allowSorting
 */
function writeCategoryItem($category, $indent = 0, $allowSorting = true) {
    echo "<li class=\"js-nestable-item nestable-item\" data-id=\"{$category['CategoryID']}\">\n";
    if ($allowSorting) {
        echo '<div class="js-nestable-handle nestable-handle">';
        echo '</div>';
    }
    echo '<div class="nestable-content plank-item">';
    if ($allowSorting) {
        echo '<div class="btn btn-icon plank-icon">';
        echo symbol('handle', t('Drag'));
        echo '</div>';
    }
    echo '<div class="plank-title">';

    if (in_array($category['DisplayAs'], ['Categories', 'Flat'])) {
        echo anchor(
            htmlspecialchars($category['Name']),
            '/vanilla/settings/categories?parent='.urlencode($category['UrlCode'])
        );
    } else {
        echo htmlspecialchars($category['Name']);
    }
    echo "</div>\n";

    echo "\n$i  <div class=\"plank-options\">";
    writeCategoryOptions($category);
    echo "</div>";
    echo '</div>';

    if (!empty($category['Children'])) {
        writeCategoryTree($category['Children'], $indent + 1);
    }

    echo "$i</li>\n";
}

/**
 *
 *
 * @param string $displayAs
 * @return string
 */
function displayAsSymbol($displayAs) {
    switch (strtolower($displayAs)) {
        case 'heading':
            return symbol('heading');
        case 'categories':
            return symbol('nested');
        case 'flat':
            return symbol('flat');
        case 'discussions':
        default:
            return symbol('discussions');
    }
}

/**
 *
 *
 * @param $name
 * @param string $alt
 * @return string
 */
function symbol($name, $alt = '') {
    if (!empty($alt)) {
        $alt = 'alt="'.htmlspecialchars($alt).'" ';
    }

    $r = <<<EOT
<svg {$alt}class="icon icon-16 icon-$name" viewBox="0 0 16 16"><use xlink:href="#$name" /></svg>
EOT;

    return $r;
}

/**
 *
 *
 * @param array $category
 */
function writeCategoryOptions($category) {
    $cdd = CategoryModel::getCategoryDropdown($category);
    echo $cdd->toString();
}

/**
 *
 *
 * @param array $ancestors
 */
function writeCategoryBreadcrumbs($ancestors) {
    echo '<div class="bigcrumbs full-border">';

    writeCategoryBreadcrumb(
        t('Home'),
        '/vanilla/settings/categories',
        empty($ancestors) ? 'last' : ''
    );

    foreach ($ancestors as $i => $ancestor) {
        if (!in_array($ancestor['DisplayAs'], ['Categories', 'Flat'])) {
            continue;
        }

        $last = $i === count($ancestors) - 1;

        writeCategoryBreadcrumb(
            htmlspecialchars($ancestor['Name']),
            '/vanilla/settings/categories?parent='.$ancestor['UrlCode'],
            $last ? 'last' : ''
        );
    }
    echo '</div>';
}

/**
 *
 *
 * @param string $text
 * @param string $uri
 * @param string $cssClass
 */
function writeCategoryBreadcrumb($text, $uri, $cssClass = '') {
    echo anchor($text, $uri, trim('crumb '.$cssClass));
}
