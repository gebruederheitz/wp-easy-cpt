<?php

namespace Gebruederheitz\Wordpress\CustomPostType;

use WP_Block_Editor_Context;
use WP_Post;

abstract class PostType implements PostTypeInterface
{
    public static $postTypeName = '';
    public static $prettyName = '';

    /** @var bool Whether to use a Gutenberg editor and call the allowed block types hook. */
    protected $withGutenberg = false;

    /** @var bool Whether to load the media picker scripts on the edit page. */
    protected $withMedia = false;

    /** @var array List of allowed block types if Gutenberg is enabled. */
    protected $allowedBlockTypes = [];

    /** @var string The translation domain to use for menu labels etc. */
    protected $translationDomain = 'ghwp';

    public static function getPostTypeName(): string
    {
        return static::$postTypeName;
    }

    public static function getPrettyName(): string
    {
        return static::$prettyName;
    }

    /**
     * Post type constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'onInit']);
        add_action('admin_init', [$this, 'onAdminInit']);
        add_action('save_post_' . static::$postTypeName, [
            $this,
            'onSavePostMeta',
        ]);

        if ($this->withMedia) {
            add_action('admin_enqueue_scripts', [
                $this,
                'onAdminEnqueueScripts',
            ]);
        }

        if ($this->withGutenberg) {
            add_filter(
                'allowed_block_types_all',
                [$this, 'onAllowedBlockTypes'],
                20,
                2,
            );
        }
    }

    /**
     * Action callback method for the "init" hook.
     */
    public function onInit()
    {
        $this->registerPostType();
    }

    /**
     * Action callback method for the "admin_init" hook.
     */
    public function onAdminInit()
    {
        $this->registerMetabox();
    }

    /**
     * Filter callback method for the "allowed_block_types" hook.
     * Allow only certain blocks to be used in Gutenberg.
     *
     * @param bool|array              $allowedBlockTypes
     * @param WP_Block_Editor_Context $context
     *
     * @return array
     */
    public function onAllowedBlockTypes(
        $allowedBlockTypes,
        WP_Block_Editor_Context $context
    ) {
        if (
            isset($context->post) &&
            $context->post->post_type !== static::$postTypeName
        ) {
            return $allowedBlockTypes;
        }
        return $this->allowedBlockTypes;
    }

    /**
     * Callback for the 'admin_enqueue_scripts' action hook.
     */
    public function onAdminEnqueueScripts(): void
    {
        wp_enqueue_media();
    }

    /**
     * Wrapper for handling post meta submissions, forwarding them to abstract
     * savePostMeta().
     */
    public function onSavePostMeta()
    {
        global $post;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post)) {
            $this->savePostMeta($post, $_POST);
        }
    }

    /**
     * Renders the meta box for users to edit additional post type fields.
     *
     * @return void
     */
    abstract public function renderMetabox();

    /**
     * Handle the submission of user edited metadata.
     *
     * @param WP_POST $post
     * @param array $data
     *
     * @return void
     */
    abstract public function savePostMeta(WP_POST $post, array $data);

    public function usesGutenberg(): bool
    {
        return $this->withGutenberg;
    }

    /**
     * Allows you to modify the arguments for the post type's registration call.
     */
    protected function editRegistrationArgs(
        PostTypeRegistrationArgs $args
    ): PostTypeRegistrationArgs {
        return $args;
    }

    /**
     * Registers the custom post type.
     */
    protected function registerPostType()
    {
        $args = $this->getRegistrationArgs();

        register_post_type(static::$postTypeName, $args);
    }

    /**
     * Registers the custom metabox for editing the meta vales.
     */
    protected function registerMetabox()
    {
        add_meta_box(
            static::$postTypeName . '_meta',
            static::$prettyName . ' Details',
            [$this, 'renderMetabox'],
            static::$postTypeName,
            'side',
            'low',
        );
    }

    private function getRegistrationArgs(): array
    {
        $args = new PostTypeRegistrationArgs($this, $this->translationDomain);
        $modifiedArgs = $this->editRegistrationArgs($args);

        return $modifiedArgs->get();
    }
}
