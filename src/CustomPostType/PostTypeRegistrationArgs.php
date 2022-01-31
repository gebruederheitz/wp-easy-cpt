<?php

namespace Gebruederheitz\Wordpress\CustomPostType;

class PostTypeRegistrationArgs
{
    /** @var PostTypeInterface */
    protected $postType;

    /** @var string */
    protected $translationDomain;

    /**
     *  An array of labels for this post type. If not set, post labels are
     * inherited for non-hierarchical types and page labels for hierarchical
     * ones. See get_post_type_labels() for a full list of supported labels:
     * https://developer.wordpress.org/reference/functions/get_post_type_labels/
     *
     * @var string[]
     */
    protected $labels = [];

    /**
     * A short descriptive summary of what the post type is.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Whether a post type is intended for use publicly either via the admin
     * interface or by front-end users. While the default settings of
     * $exclude_from_search, $publicly_queryable, $show_ui, and
     * $show_in_nav_menus are inherited from public, each does not rely on this
     * relationship and controls a very specific intention.
     * @Default false.
     *
     * @var bool
     */
    protected $public = false;

    /**
     * Whether the post type is hierarchical (e.g. page). Default false.
     *
     * @var bool
     */
    protected $hierarchical = false;

    /**
     * Whether to exclude posts with this post type from front end search
     * results.
     * @Default is the opposite value of $public.
     *
     * @var bool
     */
    protected $excludeFromSearch;

    /**
     * Whether queries can be performed on the front end for the post type as
     * part of parse_request(). Endpoints would include:
     *   + ?post_type={post_type_key}
     *   + ?{post_type_key}={single_post_slug}
     *   + ?{post_type_query_var}={single_post_slug}
     *
     *  If not set, the default is inherited from $public.
     *
     * @var bool
     */
    protected $publiclyQueryable;

    /**
     * Whether to generate and allow a UI for managing this post type in the
     * admin.
     * @Default is value of $public.
     *
     * @var bool
     */
    protected $showUi;

    /**
     * Where to show the post type in the admin menu. To work, $show_ui must be
     * true. If true, the post type is shown in its own top level menu. If false,
     * no menu is shown. If a string of an existing top level menu (e.g.
     * 'tools.php' or 'edit.php?post_type=page'), the post type will be placed
     * as a sub-menu of that.
     * @Default is value of $show_ui.
     *
     * @var bool|string
     */
    protected $showInMenu;

    /**
     * Makes this post type available for selection in navigation menus.
     * @Default is value of $public.
     *
     * @var bool
     */
    protected $showInNavMenus;

    /**
     * Makes this post type available via the admin bar.
     * @Default is value of $show_in_menu.
     *
     * @var bool
     */
    protected $showInAdminBar;

    /**
     * Whether to include the post type in the REST API. Set this to true for
     * the post type to be available in the block editor.
     *
     * @var bool
     */
    protected $showInRest;

    /**
     * To change the base url of REST API route.
     * @Default is $post_type (i.e. PostType::$postTypeName).
     *
     * @var string
     */
    protected $restBase;

    /**
     * REST API Controller class name.
     * @Default is 'WP_REST_Posts_Controller'.
     *
     * @var string
     */
    protected $restControllerClass;

    /**
     * The position in the menu order the post type should appear. To work,
     * $show_in_menu must be true. Default null (at the bottom).
     *
     * @var int
     */
    protected $menuPosition;

    /**
     * The url to the icon to be used for this menu.
     *  + Pass a base64-encoded SVG using a data URI, which will be colored to
     *    match the color scheme -- this should begin with 'data:image/svg+xml;base64,'.
     *  + Pass the name of a Dashicons helper class to use a font icon, e.g.
     *    'dashicons-chart-pie'.
     *  + Pass 'none' to leave div.wp-menu-image empty so an icon can be added
     *    via CSS.
     * @Defaults to use the posts icon.
     *
     * @var string
     */
    protected $menuIcon;

    /**
     * The string to use to build the read, edit, and delete capabilities. May
     * be passed as an array to allow for alternative plurals when using this
     * argument as a base to construct the capabilities, e.g.
     * array('story', 'stories').
     * @Default 'post'.
     *
     * @var string
     */
    protected $capabilityType;

    /**
     * Array of capabilities for this post type. $capability_type is used as a
     * base to construct capabilities by default. See get_post_type_capabilities():
     * https://developer.wordpress.org/reference/functions/get_post_type_capabilities/
     *
     * @var string[]
     */
    protected $capabilities;

    /**
     * Core feature(s) the post type supports. Serves as an alias for calling
     * add_post_type_support() directly. Core features include
     *   + 'title',
     *   + 'editor',
     *   + 'comments',
     *   + 'revisions',
     *   + 'trackbacks',
     *   + 'author',
     *   + 'excerpt',
     *   + 'page-attributes',
     *   + 'thumbnail',
     *   + 'custom-fields',
     *   + 'post-formats'.
     *
     * Additionally, the 'revisions' feature dictates whether the post type will
     * store revisions, and the 'comments' feature dictates whether the comments
     * count will show on the edit screen. A feature can also be specified as an
     * array of arguments to provide additional information about supporting
     * that feature. Example: array( 'my_feature', array( 'field' => 'value' ) ).
     * @Default is an array containing 'title' and 'editor'.
     *
     * The initial value of 'editor' depends on the value of
     * PostType::$withGutenberg on the PostType passed to the constructor.
     *
     * @var array
     */
    protected $supports = [
        'title',
        'editor',
    ];

    /**
     * Provide a callback function that sets up the meta boxes for the edit form.
     * Do remove_meta_box() and add_meta_box() calls in the callback.
     * @Default null.
     *
     * @var callable
     */
    protected $registerMetaBoxCb;

    /**
     * An array of taxonomy identifiers that will be registered for the post
     * type. Taxonomies can be registered later with register_taxonomy() or
     * register_taxonomy_for_object_type().
     *
     * @var string[]
     */
    protected $taxonomies;

    /**
     * Whether there should be post type archives, or if a string, the archive
     * slug to use. Will generate the proper rewrite rules if $rewrite is
     * enabled.
     * @Default false.
     *
     * @var bool|string
     */
    protected $hasArchive;

    /**
     * Triggers the handling of rewrites for this post type. To prevent rewrite,
     * set to false. Defaults to true, using $post_type as slug. To specify
     * rewrite rules, an array can be passed with any of these keys:
     *
     *   - 'slug'
     *      (string) Customize the permastruct slug. Defaults to $post_type key.
     *   - 'with_front'
     *      (bool) Whether the permastruct should be prepended with
     *      WP_Rewrite::$front. Default true.
     *   - 'feeds'
     *      (bool) Whether the feed permastruct should be built for this post
     *      type. Default is value of $has_archive.
     *   - 'pages'
     *      (bool) Whether the permastruct should provide for pagination.
     *      Default true.
     *   - 'ep_mask'
     *      (int) Endpoint mask to assign. If not specified and permalink_epmask
     *      is set, inherits from $permalink_epmask. If not specified and
     *      permalink_epmask is not set, defaults to EP_PERMALINK.
     * @var bool|array
     */
    protected $rewrite;

    /**
     * Sets the query_var key for this post type. Defaults to $post_type key.
     * If false, a post type cannot be loaded at ?{query_var}={post_slug}. If
     * specified as a string, the query ?{query_var_string}={post_slug} will be
     * valid.
     *
     * @var string|bool
     */
    protected $queryVar;

    /**
     * Whether to allow this post type to be exported.
     * @Default true.
     *
     * @var bool
     */
    protected $canExport;

    /**
     * Whether to delete posts of this type when deleting a user.
     *  - If true, posts of this type belonging to the user will be moved to
     *    Trash when the user is deleted.
     *  - If false, posts of this type belonging to the user will *not* be
     *    trashed or deleted.
     *  - If not set (the default), posts are trashed if post type supports the
     *    'author' feature. Otherwise posts are not trashed or deleted.
     *
     * @Default null.
     *
     * @var bool
     */
    protected $deleteWithUser;

    /**
     * Array of blocks to use as the default initial state for an editor session.
     * Each item should be an array containing block name and optional attributes.
     *
     * @var array
     */
    protected $template;

    /**
     * Whether the block template should be locked if $template is set.
     *   - If set to 'all', the user is unable to insert new blocks, move
     *     existing blocks and delete blocks.
     *   - If set to 'insert', the user is able to move existing blocks but
     *     is unable to insert new blocks and delete blocks.
     * @Default false.
     *
     * @var string|false
     */
    protected $templateLock;

    public function __construct(PostTypeInterface $postType, string $translationDomain)
    {
        $this->postType = $postType;
        $this->translationDomain = $translationDomain;

        $this->setDescription($postType::getPrettyName() . ' custom post type');
        $this->setLabels(
            [
                'name' => _x(
                    $postType::getPrettyName(),
                    $postType::getPrettyName() . ' post type general name',
                    $translationDomain
                ),
                'singular_name' => _x(
                    $postType::getPrettyName(),
                    $postType::getPrettyName() . ' post type singular name',
                    $translationDomain
                ),
            ]
        );

        if ($postType->usesGutenberg()) {
            $this->setShowInRest(true);
        } else {
            $this->removeEditorSupport();
        }
    }

    /**
     * @param string[] $labels
     *
     * @return PostTypeRegistrationArgs
     */
    public function setLabels(array $labels): PostTypeRegistrationArgs
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return PostTypeRegistrationArgs
     */
    public function setDescription(string $description): PostTypeRegistrationArgs
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param bool $public
     *
     * @return PostTypeRegistrationArgs
     */
    public function setPublic(bool $public): PostTypeRegistrationArgs
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @param bool $hierarchical
     *
     * @return PostTypeRegistrationArgs
     */
    public function setHierarchical(bool $hierarchical): PostTypeRegistrationArgs
    {
        $this->hierarchical = $hierarchical;

        return $this;
    }

    /**
     * @param bool $excludeFromSearch
     *
     * @return PostTypeRegistrationArgs
     */
    public function setExcludeFromSearch(bool $excludeFromSearch): PostTypeRegistrationArgs
    {
        $this->excludeFromSearch = $excludeFromSearch;

        return $this;
    }

    /**
     * @param bool $publiclyQueryable
     *
     * @return PostTypeRegistrationArgs
     */
    public function setPubliclyQueryable(bool $publiclyQueryable): PostTypeRegistrationArgs
    {
        $this->publiclyQueryable = $publiclyQueryable;

        return $this;
    }

    /**
     * @param bool $showUi
     *
     * @return PostTypeRegistrationArgs
     */
    public function setShowUi(bool $showUi): PostTypeRegistrationArgs
    {
        $this->showUi = $showUi;

        return $this;
    }

    /**
     * @param bool|string $showInMenu
     *
     * @return PostTypeRegistrationArgs
     */
    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    /**
     * @param bool $showInNavMenus
     *
     * @return PostTypeRegistrationArgs
     */
    public function setShowInNavMenus(bool $showInNavMenus): PostTypeRegistrationArgs
    {
        $this->showInNavMenus = $showInNavMenus;

        return $this;
    }

    /**
     * @param bool $showInAdminBar
     *
     * @return PostTypeRegistrationArgs
     */
    public function setShowInAdminBar(bool $showInAdminBar): PostTypeRegistrationArgs
    {
        $this->showInAdminBar = $showInAdminBar;

        return $this;
    }

    /**
     * @param bool $showInRest
     *
     * @return PostTypeRegistrationArgs
     */
    public function setShowInRest(bool $showInRest): PostTypeRegistrationArgs
    {
        $this->showInRest = $showInRest;

        return $this;
    }

    /**
     * @param string $restBase
     *
     * @return PostTypeRegistrationArgs
     */
    public function setRestBase(string $restBase): PostTypeRegistrationArgs
    {
        $this->restBase = $restBase;

        return $this;
    }

    /**
     * @param string $restControllerClass
     *
     * @return PostTypeRegistrationArgs
     */
    public function setRestControllerClass(string $restControllerClass): PostTypeRegistrationArgs
    {
        $this->restControllerClass = $restControllerClass;

        return $this;
    }

    /**
     * @param int $menuPosition
     *
     * @return PostTypeRegistrationArgs
     */
    public function setMenuPosition(int $menuPosition): PostTypeRegistrationArgs
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * @param string $menuIcon
     *
     * @return PostTypeRegistrationArgs
     */
    public function setMenuIcon(string $menuIcon): PostTypeRegistrationArgs
    {
        $this->menuIcon = $menuIcon;

        return $this;
    }

    /**
     * @param string $capabilityType
     *
     * @return PostTypeRegistrationArgs
     */
    public function setCapabilityType(string $capabilityType): PostTypeRegistrationArgs
    {
        $this->capabilityType = $capabilityType;

        return $this;
    }

    /**
     * @param string[] $capabilities
     *
     * @return PostTypeRegistrationArgs
     */
    public function setCapabilities(array $capabilities): PostTypeRegistrationArgs
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    /**
     * @param array $supports
     *
     * @return PostTypeRegistrationArgs
     */
    public function setSupports(array $supports): PostTypeRegistrationArgs
    {
        $this->supports = $supports;

        return $this;
    }

    /**
     * @param callable $registerMetaBoxCb
     *
     * @return PostTypeRegistrationArgs
     */
    public function setRegisterMetaBoxCb(callable $registerMetaBoxCb
    ): PostTypeRegistrationArgs {
        $this->registerMetaBoxCb = $registerMetaBoxCb;

        return $this;
    }

    /**
     * @param string[] $taxonomies
     *
     * @return PostTypeRegistrationArgs
     */
    public function setTaxonomies(array $taxonomies): PostTypeRegistrationArgs
    {
        $this->taxonomies = $taxonomies;

        return $this;
    }

    /**
     * @param bool|string $hasArchive
     *
     * @return PostTypeRegistrationArgs
     */
    public function setHasArchive($hasArchive)
    {
        $this->hasArchive = $hasArchive;

        return $this;
    }

    /**
     * @param array|bool $rewrite
     *
     * @return PostTypeRegistrationArgs
     */
    public function setRewrite($rewrite)
    {
        $this->rewrite = $rewrite;

        return $this;
    }

    /**
     * @param bool|string $queryVar
     *
     * @return PostTypeRegistrationArgs
     */
    public function setQueryVar($queryVar)
    {
        $this->queryVar = $queryVar;

        return $this;
    }

    /**
     * @param bool $canExport
     *
     * @return PostTypeRegistrationArgs
     */
    public function setCanExport(bool $canExport): PostTypeRegistrationArgs
    {
        $this->canExport = $canExport;

        return $this;
    }

    /**
     * @param bool $deleteWithUser
     *
     * @return PostTypeRegistrationArgs
     */
    public function setDeleteWithUser(bool $deleteWithUser): PostTypeRegistrationArgs
    {
        $this->deleteWithUser = $deleteWithUser;

        return $this;
    }

    /**
     * @param array $template
     *
     * @return PostTypeRegistrationArgs
     */
    public function setTemplate(array $template): PostTypeRegistrationArgs
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param false|string $templateLock
     *
     * @return PostTypeRegistrationArgs
     */
    public function setTemplateLock($templateLock)
    {
        $this->templateLock = $templateLock;

        return $this;
    }

    public function setPluralLabel(string $label): self
    {
        $this->labels['name'] = _x(
            $label,
            $this->postType::getPrettyName() . ' post type general name',
            $this->translationDomain
        );

        return $this;
    }

    public function removeEditorSupport(): self
    {
        $this->supports = array_diff($this->supports, ['editor']);

        return $this;
    }

    public function removeTitleSupport(): self
    {
        $this->supports = array_diff($this->supports, ['title']);

        return $this;
    }

    public function addRevisionSupport(): self
    {
        array_push($this->supports, 'revisions');

        return $this;
    }

    public function addCommentSupport(): self
    {
        array_push($this->supports, 'comments');

        return $this;
    }

    public function addTrackbacksSupport(): self
    {
        array_push($this->supports, 'trackbacks');

        return $this;
    }

    public function addAuthorSupport(): self
    {
        array_push($this->supports, 'author');

        return $this;
    }

    public function addExcerptSupport(): self
    {
        array_push($this->supports, 'excerpt');

        return $this;
    }

    public function addThumbnailSupport(): self
    {
        array_push($this->supports, 'thumbnail');

        return $this;
    }

    public function addSupport(string $supportType): self
    {
        array_push($this->supports, $supportType);

        return $this;
    }

    public function makePublic(): self
    {
        $this->public = true;

        return $this;
    }

    public function get(): array
    {
        $args = [
            'supports' => $this->supports,
            'description' => $this->description,
            'public' => $this->public,
            'labels' => $this->labels,
        ];

        $props = [
            'hierarchical' => 'hierarchical',
            'excludeFromSearch' => 'exclude_from_search',
            'publiclyQueryable' => '',
            'showUi' => 'show_ui',
            'showInMenu' => 'show_in_menu',
            'showInNavMenus' => 'show_in_nav_menus',
            'showInAdminBar' => 'show_in_admin_bar',
            'showInRest' => 'show_in_rest',
            'restBase' => 'rest_base',
            'restControllerClass' => 'rest_controller_class',
            'menuPosition' => 'menu_position',
            'menuIcon' => 'menu_icon',
            'capabilityType' => 'capability_type',
            'capabilities' => 'capabilities',
            'registerMetaBoxCb' => 'register_meta_box_cb',
            'taxonomies' => 'taxonomies',
            'hasArchive' => 'has_archive',
            'rewrite' => 'rewrite',
            'queryVar' => 'query_var',
            'canExport' => 'can_export',
            'deleteWithUser' => 'delete_with_user',
            'template' => 'template',
            'templateLock' => 'template_lock',
        ];

        foreach ($props as $property => $argument) {
            if (isset($this->{$property})) {
                $args[$argument] = $this->{$property};
            }
        }

        return $args;
    }
}
