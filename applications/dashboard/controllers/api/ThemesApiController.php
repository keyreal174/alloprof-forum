<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

use Garden\Web\Data;
use Garden\Web\Exception\ServerException;
use Vanilla\Models\ThemeModel;

/**
 * API Controller for the `/themes` resource.
 */
class ThemesApiController extends AbstractApiController {
    use ThemesApiSchemes;

    // Theming
    const GET_THEME_ACTION = "@@themes/GET_DONE";
    const GET_THEME_VARIABLES_ACTION = "@@themes/GET_VARIABLES_DONE";

    /** @var ThemeModel */
    private $themeModel;

    /**
     * ThemesApiController constructor.
     * @param ThemeModel $themeModel
     */
    public function __construct(ThemeModel $themeModel) {
        $this->themeModel = $themeModel;
    }

    /**
     * Get the content type for the provided asset.
     *
     * @param string $assetKey
     * @return string
     */
    private function contentTypeByAsset(string $assetKey): string {
        $types = [
            "fonts" => "application/json",
            "footer" => "text/html",
            "header" => "text/html",
            "javascript" => "application/javascript",
            "scripts" => "application/json",
            "styles" => "text/css",
            "variables" => "application/json",
        ];
        $basename = pathinfo($assetKey, PATHINFO_FILENAME);
        if (!array_key_exists($basename, $types)) {
            throw new ServerException("Could not find a content type for the asset: {$basename}");
        }
        return $types[$basename];
    }

    /**
     * Get a theme assets.
     *
     * @param string $themeKey The unique theme key or theme ID.
     * @return array
     */
    public function get(string $themeKey): array {
        $this->permission();
        $out = $this->themeResultSchema('out');

        $themeWithAssets = $this->themeModel->getThemeWithAssets($themeKey);
        $result = $out->validate($themeWithAssets);
        return $result;
    }

    /**
     * Create new theme.
     *
     * @param array $body Array of incoming params.
     *        fields: name (required)
     * @return array
     */
    public function post(array $body): array {
        $this->permission("Garden.Settings.Manage");

        $in = $this->themePostSchema('in')->setDescription('Create new custom theme.');

        $out = $this->themeResultSchema('out');

        $body = $in->validate($body);

        $normalizedTheme = $this->themeModel->postTheme($body);

        $theme = $out->validate($normalizedTheme);
        return $theme;
    }


    /**
     * Update theme name by ID.
     *
     * @param int $themeID Theme ID
     * @param array $body Array of incoming params.
     *        fields: name (required)
     * @return array
     */
    public function patch(int $themeID, array $body): array {
        $this->permission("Garden.Settings.Manage");
        $in = $this->themePostSchema('in')->setDescription('Update theme name.');
        $out = $this->themeResultSchema('out');
        $body = $in->validate($body);

        $normalizedTheme = $this->themeModel->patchTheme($themeID, $body);

        $theme = $out->validate($normalizedTheme);
        return $theme;
    }

    /**
     * Delete theme by ID.
     *
     * @param int $themeID Theme ID
     */
    public function delete(int $themeID) {
        $this->permission("Garden.Settings.Manage");
        $this->themeModel->deleteTheme($themeID);
    }

    /**
     * Set theme as "current" theme.
     *
     * @param array $body Array of incoming params.
     *        fields: themeID (required)
     * @return array
     */
    public function put_current(array $body): array {
        $this->permission("Garden.Settings.Manage");
        $in = $this->themePutCurrentSchema('in')->setDescription('Set current theme.');
        $out = $this->themeResultSchema('out');
        $body = $in->validate($body);

        $theme = $this->themeModel->setCurrentTheme($body['themeID']);
        $theme = $out->validate($theme);
        return $theme;
    }

    /**
     * Get "current" theme.
     *
     * @return array
     */
    public function get_current(): ?array {
        $this->permission();
        $in = $this->schema([], 'in')->setDescription('Get current theme.');
        $out = $this->themeResultSchema('out');

        $theme = $this->themeModel->getCurrentTheme();

        return $out->validate($theme);
    }

    /**
     * PUT theme asset (update existing or create new if asset does not exist).
     *
     * @param int $themeID The unique theme ID.
     * @param string $assetKey Unique asset key (ex: header.html, footer.html, fonts.json, styles.css)
     * @param array $body Array of incoming params.
     *              Should have 'data' key with content for asset.
     *
     * @return array
     */
    public function put_assets(int $themeID, string $assetKey, array $body): array {
        $this->permission("Garden.Settings.Manage");

        $in = $this->schema($this->assetsPutSchema(), 'in')->setDescription('PUT theme asset.');
        $out = $this->schema($this->assetsSchema(), 'out');

        $body = $in->validate($body);

        $pathInfo = pathinfo($assetKey);
        if (isset(ThemeModel::ASSET_LIST[$pathInfo['filename']])) {
            if ($pathInfo['basename'] === ThemeModel::ASSET_LIST[$pathInfo['filename']]['file']) {
                $asset = $this->themeModel->setAsset($themeID, $pathInfo['filename'], $body['data']);
            } else {
                throw new ClientException('Unknown asset file name: "'.$pathInfo['basename'].'".'.
                    'Try: '.ThemeModel::ASSET_LIST[$pathInfo['filename']]['file']);
            }
        } else {
            throw new \Garden\Schema\ValidationException('Unknown asset "'.$pathInfo['filename'].'" field.'.
                'Should be one of: '.implode(array_column(ThemeModel::ASSET_LIST, 'file')));
        }
        return $out->validate($asset);
    }

    /**
     * DELETE theme asset.
     *
     * @param int $themeID The unique theme ID.
     * @param string $assetKey Unique asset key (ex: header.html, footer.html, fonts.json, styles.css)
     */
    public function delete_assets(int $themeID, string $assetKey) {
        return $this->getCustomThemeProvider()->delete_assets($themeID, $assetKey);
    }

    /**
     * Get theme asset.
     *
     * @param string $id The unique theme key or theme ID (ex: keystone).
     * @param string $assetKey Unique asset key (ex: header, footer, fonts, styles)
     *        Note: assetKey can be filename (ex: header.html, styles.css)
     *              in that case file content returned instaed of json structure
     * @link https://github.com/vanilla/roadmap/blob/master/theming/theming-data.md#api
     *
     * @return array|Data
     */
    public function get_assets(string $id, string $assetKey) {
        $this->permission();
        $content = $this->themeModel->getAssetData($id, $assetKey);
        $contentType = $this->contentTypeByAsset($assetKey);
        $result = new Data($content);
        return $result->setHeader("Content-Type", $contentType);
    }
}
