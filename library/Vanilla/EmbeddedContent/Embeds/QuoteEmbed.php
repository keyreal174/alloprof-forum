<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\EmbeddedContent\Embeds;

use Garden\Schema\Schema;
use Vanilla\EmbeddedContent\AbstractEmbed;
use Vanilla\EmbeddedContent\EmbedUtils;
use Vanilla\Models\UserFragmentSchema;

/**
 * Fallback scraped link embed.
 */
class QuoteEmbed extends AbstractEmbed {

    const TYPE = "quote";

    /**
     * @inheritdoc
     */
    protected function getAllowedTypes(): array {
        return [self::TYPE];
    }

    /**
     * @inheritdoc
     */
    public function normalizeData(array $data): array {
        // Handle the IDs
        $discussionID = $data['attributes']['discussionID'] ?? null;
        $commentID = $data['attributes']['commentID'] ?? null;

        if ($discussionID !== null) {
            $data['recordID'] = $discussionID;
            $data['recordType'] = 'discussion';
        } elseif ($commentID !== null) {
            $data['recordID'] = $commentID;
            $data['recordType'] = 'comment';
        }

        $data = EmbedUtils::remapProperties($data, [
            'name' => 'attributes.name',
            'bodyRaw' => 'attributes.bodyRaw',
            'format' => 'attributes.format',
            'dateInserted' => 'attributes.dateInserted',
            'insertUser' => 'attributes.insertUser',
        ]);

        // Format the body.
        if (!isset($data['body']) && isset($data['bodyRaw'])) {
            $data['body'] = \Gdn_Format::quoteEmbed($data['bodyRaw'], $data['format']);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function schema(): Schema {
        return Schema::parse([
            'body:s', // The body is need currnetly during edit mode,
            // to prevent needing extra server roundtrips to render them.
            'bodyRaw:s|a', // Raw body is the source of truth for the embed.
            'format:s',
            'dateInserted:dt',
            'insertUser' => new UserFragmentSchema(),
        ]);
    }
}
