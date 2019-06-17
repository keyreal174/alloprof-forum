<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\EmbeddedContent\Embeds;

use Garden\Web\Exception\NotFoundException;
use Garden\Web\RequestInterface;
use Vanilla\EmbeddedContent\AbstractEmbed;
use Vanilla\EmbeddedContent\AbstractEmbedFactory;
use Vanilla\Web\Asset\SiteAsset;

/**
 * Quote embed factory for comments.
 */
class DiscussionEmbedFactory extends AbstractEmbedFactory {

    /** @var RequestInterface */
    private $request;

    /** @var \DiscussionsApiController */
    private $discussionApi;

    /**
     * DI
     *
     * @param RequestInterface $request
     * @param \DiscussionsApiController $discussionApi
     */
    public function __construct(RequestInterface $request, \DiscussionsApiController $discussionApi) {
        $this->request = $request;
        $this->discussionApi = $discussionApi;
    }

    /**
     * @return array
     */
    protected function getSupportedDomains(): array {
        return [
            $this->request->getHost(),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getSupportedPathRegex(string $domain = ''): string {
        // We need ot be sure to the proper web root here.
        $root = SiteAsset::joinWebPath($this->request->getRoot(), '/discussion');
        $root = str_replace('/', '\/', $root);

        return "/^$root\/(?<discussionID>\d+)/i";
    }

    /**
     * @inheritdoc
     */
    public function createEmbedForUrl(string $url): AbstractEmbed {
        preg_match($this->getSupportedPathRegex(), $url, $matches);
        $id = $matches['discussionID'] ?? null;

        if ($id === null) {
            throw new NotFoundException('Comment');
        }

        $discussion = $this->discussionApi->get_quote($id);
        return new QuoteEmbed($discussion);
    }

}
