# Wordpress Easy Custom Post Type

_Easy to use tools for a robust Wordpress custom post type setup._

---

## Installation

via composer:
```shell
> composer require gebruederheitz/wp-easy-cpt
```

Make sure you have Composer autoload or an alternative class loader present.


## Usage

### Post Type abstract class

```php
use Gebruederheitz\Wordpress\CustomPostType\PostType;
use Gebruederheitz\Wordpress\CustomPostType\PostTypeRegistrationArgs;

class NewsPostType extends PostType 
{
    /** 
     * @required Supply a slug-type name which will be used in the DB amongst 
     *           other places 
     */
    public static $postTypeName = 'news';
    
    /** 
     * @required The name users will see 
     */
    public static $prettyName = 'News';
    
    /**
     * @optional Where the metabox will appear: one of 'side', 'normal', 'advanced'
     * @default 'side'
     */
    public static $context = 'normal';

    /** 
     * @var bool Whether to use a Gutenberg editor and call the allowed block 
     *           types hook. Set to "false" by default, so if you don't need
     *           Gutenberg you can just leave this out.
     */
    protected $withGutenberg = true;

    /** 
     * @var bool Whether to load the media picker scripts on the edit page. 
     *           If you don't need to use a media picker, you can leave this
     *           bit out. 
     */
    protected $withMedia = true;

    /** 
     * @var array List of allowed block types if Gutenberg is enabled. If you
     *            did not set $withGutenberg to `true` you won't need this.
     *            Otherwise supply a string-array of block names 
     *            (e.g. `core/button`). 
     */
    protected $allowedBlockTypes = [
        'core/paragraph',
        'core/columns',
    ];
    
    
    /** 
     * @var string The translation domain to use for menu labels etc. 
     * 
     * If you are using the "ghwp" domain, you can skip this setting, otherwise
     * set it to your theme / plugin's i18n domain.
     */
    protected $translationDomain = 'ghwp';
    
    // -------------------------------------------------------------------------
    // There are only two methods you need to define, and one you will want to
    // override:
    // -------------------------------------------------------------------------
    
    /**
     * Renders the meta box for users to edit additional post type fields.
     *
     * @return void
     */
    public function renderMetabox() 
    {
        /** @var WP_Post */
        global $post;
        
        // ---------------------------------------------------------------------
        // You could go old-school
        // ---------------------------------------------------------------------
        ?>
            <input name="postid" type="text" value="<?php echo $post->ID; ?>" />
        <?php

        // or use a library like cmb2
        $metabox = new_cmb2_box([
            'id' => self::$postTypeName . 'something',
            'title' => 'Details',
        ]);
        
        // ---------------------------------------------------------------------
        // or use the dependency-free MetaForms class and maybe even combine it
        // with the repository (more below)
        // ---------------------------------------------------------------------
    
        /** @var Newspost $news */
        $news = NewspostRepository::getInstance()->find($post->ID);

        MetaForms::renderTextInputField(
            Newspost::tagMetaFieldName,
            $news->getTagsAsString(),
            'Tags (separate with semicolons)',
            false
        );
    }

    /**
     * Handle the submission of user edited metadata.
     *
     * @param WP_POST $post
     * @param array $data 
     *
     * @return void
     */
    public function savePostMeta($post, $data) 
    {
        /** @var Newspost $news */
        $news = NewspostRepository::getInstance()->find($post->ID);

        $news->setTagsFromString($data[Newspost::tagMetaFieldName] ?? '');

        NewspostRepository::getInstance()->save($news)->flush();
    }

    /*
     * The PostType class handles the registration for you. For easy access and
     * type definitions it uses the PostTypeRegistrationArgs configuration
     * object.
     * To modify the arguments passed to `register_post_type` you can override
     * the method editRegistrationArgs() and call the fluent setters on the
     * PostTypeRegistrationArgs object provided.
     */

    /**
     * Modify the arguments for the post type's registration call.
     */
    protected function editRegistrationArgs(PostTypeRegistrationArgs $args): PostTypeRegistrationArgs 
    {
         $args->addAuthorSupport();
         // Setters in PostTypeRegistrationArgs can be chained:
         $args->setCanExport(true)
             ->setPluralLabel('Newsposts')
             ->makePublic();
         
         return $args;
    }

}
```


### Using the entity repository

The entity repository caches database calls for post type entities and helps
with normalization of post type data.

As a first step, you will have to define your post type entity (and potentially
sub-entities) implementing the `StorableEntity` interface:

```php
use Gebruederheitz\Wordpress\Domain\StorableEntity;

class Newspost implements StorableEntity
{
    /** @var int|null */
    private $postId;

    /** @var string */
    private $title = '';

    /** @var array */
    private $tags = [];

    /*
     * -------------------------------------------------------------------------
     * For easier access, and to avoid duplication and typo-style errors, we
     * define the post_meta keys as class constants. It's up to you if you
     * want to adopt this pattern.
     * You are encouraged to prefix your post_meta keys.
     * -------------------------------------------------------------------------
     */

    /** @var string */
    public const tagMetaFieldName = 'ghwp-faq-tags';
    
    /*
     * -------------------------------------------------------------------------
     * Construct the entity from the post object and the associated raw
     * post_meta.
     * -------------------------------------------------------------------------
     */
    
    public function __construct(WP_Post $post = null, $meta = []) {
        $this->postId = $post->ID ?? null;
        $this->title  = $post->post_title ?? '';
        $this->tags   = $this->unserializeTags($meta[self::tagMetaFieldName] ?? '');
    }
    
    /*
     * -------------------------------------------------------------------------
     * How you approach this bit is up to you. We'll be using a getter / setter
     * pattern in this example. These methods will be used wherever the CPT
     * is going to be consumed – template partials for instance.
      * -------------------------------------------------------------------------
    */

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): Newspost
    {
        $this->postId = $postId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Newspost
    {
        $this->title = $title;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTagsAsString(): string
    {
        return $this->serializeTags($this->tags);
    }

    public function setTags(array $tags): Newspost
    {
        $this->tags = $tags;

        return $this;
    }

    public function setTagsFromString(string $tags): Newspost
    {
        $this->tags = $this->unserializeTags($tags);

        return $this;
    }
    
    /*
     * -------------------------------------------------------------------------
     * Implement the toMetaValues() method: Return an associative array of 
     * post_meta keys with the corresponding string value.
     * -------------------------------------------------------------------------
     */

    public function toMetaValues(): array
    {
        return [
            self::tagMetaFieldName => $this->serializeTags($this->tags),
        ];
    }
    
    /*
     * -------------------------------------------------------------------------
     * We're using some utility functions here; you could also use JSON or
     * any other serialization format. Beware however, that JSON might cause
     * issues with escaped quoting – your best bet is to base64_encode() your
     * JSON before storing and base64_decode() right after retrieving the values.
     * 
     * Alternatively you can work around this using wp_slash() on already
     * escaped JSON strings: 
     * https://developer.wordpress.org/reference/functions/update_post_meta/#character-escaping
     * -------------------------------------------------------------------------
     */

    private function serializeTags(array $tags): string
    {
        return join(';', $this->tags);
    }

    private function unserializeTags(string $tags): array
    {
        return array_map('trim', explode(';', $tags));
    }
}
```

You can then create a repository for this new entity:

```php
use Gebruederheitz\Wordpress\Domain\CustomPostTypeRepository;

class NewspostRepository extends CustomPostTypeRepository
{
    /*
     * -------------------------------------------------------------------------
     * First off, define your meta key. Use a prefix. Prefix that with an
     * underscore to hide the metadata from WP menus.
     * -------------------------------------------------------------------------
     */
    public static $metaKey = '_ghwp_news';
    
    /*
     * -------------------------------------------------------------------------
     * Now define the entity and post type classes you want the repository
     * to use. The $entityClass needs to implement the StorableEntity interface,
     * while $postTypeClass must implement PostTypeInterface.
     * -------------------------------------------------------------------------
    */
     
      /** @var string */
    protected static $entityClass = Newspost::class;

    /** @var string */
    protected static $postTypeClass = NewsPostType::class;
    
    /*
     * -------------------------------------------------------------------------
     * You can override the type for the internal $entities property to get
     * better type hinting:
     * -------------------------------------------------------------------------
     */
    /** @var Newspost[] */
    protected $entities = [];
}
```

And basically you're done. You might want to override some methods if you're
doing anything out of the ordinary like:

 - large entities with multiple properties that you want to store in separate
   meta fields
 - work on meta fields for anything that's not an entire custom post type
 - ...

You can find examples [below](#entity-repository-examples)


### Non-CPT use of the entity repository

The `CustomPostTypeRepository` class is closely coupled to the `PostType` entity
class, but sometimes you may wish to use an entity repository without this
close association. You could use it for storing meta fields on a regular
`post` or `page` for example.

In these scenarios you can use the base class `AbstractRepository`, which does
not constrict you to a single post type:

```php
use Gebruederheitz\Wordpress\Domain\AbstractRepository;

class ViewCountRepository extends AbstractRepository
{
    /*
     * -------------------------------------------------------------------------
     * Here, you do not need to provide a CPT, only the meta field key and the
     * entity class this repository will manage.
     * -------------------------------------------------------------------------
     */
    public static $metaKey = 'ghwp_view_count';
    protected static $entityClass = ViewCount::class;
    
    /* 
     * -------------------------------------------------------------------------
     * You will have to implement the abstract method `getPosts()` in order
     * to define the selection your entity is based on. We could, for instance,
     * introduce a view counter on all posts with a tag "counted", and by
     * default sort them alphabetically by title.
     * -------------------------------------------------------------------------
     */
     
    /**
     * Retrieve all posts that will be processed in getAllFromDB(). Use this
     * method to fetch the list of posts relevant to you.
     * If you're using a custom post type with
     * Gebruederheitz\Wordpress\CustomPostType\PostType this method has a basic
     * implementation in CustomPostTypeRepository.
     *
     * @return WP_Post[]
     */
    protected static function getPosts(): array {
        return get_posts(
            [
                'post_type' => 'post',
                'tag' => 'counted',
                'orderby' => 'title',
                'numberposts' => -1,
            ]
        );
    }
}
```


### Entity repository examples

#### Example: `getPosts()` override

```php
    protected static function getPosts(): array
    {
        // Example: Sort entries by the meta value we define
        return get_posts(
            'post_type' => 'post',
            'tag' => 'counted',
            'orderby' => 'meta_value_num title',
            'meta_key' => self::$metaKey,
            'order' => 'ASC',
        )
        
        // Example: Retrieve all posts of a custom type ordered by a user-defined
        //          index stored in a separate meta field (using
        //          CustomPostTypeRepository)
        return get_posts(
            [
                'post_type'       => MyCustomPostType::$postTypeName,
                'orderby'         => 'meta_value_num title',
                'meta_key'        => self::$metaKey . '_order',
                'order'           => 'ASC',
                'numberposts'     => -1,
            ]
        );
        
        // This is the default for CustomPostTypeRepository – retrieves all
        // posts for the CPT class you set in $postTypeClass.
        return get_posts(
            [
                'post_type' => call_user_func([static::$postTypeClass, 'getPostTypeName']),
                'numberposts' => -1,
            ]
        );
    }
```

#### Example: Overriding `entityFromPostId` when storing data in separate meta fields (cmb2 etc.)

```php

    /**
     * Instantiates an entity instance from a post ID.
     *
     * @param int $postId
     * @param ?WP_Post $post
     *
     * @return StorableEntity An instance of the entity type defined in static::$entityClass
     */
    protected static function entityFromPostId(int $postId, WP_Post $post = null): StorableEntity
    {
        if (empty($post)) {
            $post = get_post($postId);
        }

        $meta = self::getMetaValues($postId);
        // So far it's the default behaviour of this method. We get the post
        // object if it hasn't been passed, and get the meta values for
        // static::$metaKey.
        //
        // Now we add some meta values stored separately, e.g. when using cmb2 /
        // CustomMetaBoxes2
        $meta['some-other-key'] = get_post_meta($postId, '_some-other-key', true);
        
        // When we're done, we instantiate an object from the entity class,
        // passing it the $meta array we extended and return the entity:  
        return new static::$entityClass($post, $meta);
    }
```


#### Example: `persist()` override when storing multiple meta fields

```php
     /**
     * Save a single Entity's meta fields to the database. Writes the common
     * fields to self::$metaKey and the "order" field's value to
     * "{$metaKey}_order" to allow custom sorting.
     *
     * @param StorableEntity $item The Entity to be persisted to the DB.
     */
    protected function persist(StorableEntity $item): void
    {
        update_post_meta($item->getPostId(), self::$metaKey, $item->toMetaValues());
        update_post_meta($item->getPostId(), self::$metaKey . '_order', $item->getOrder());
    }
}
```


## Development

### Dependencies

 - PHP >= 7.4
 - [Composer 2.x](https://getcomposer.org)
 - [NVM](https://github.com/nvm-sh/nvm) and nodeJS LTS (v16.x)
 - Nice to have: GNU Make (or drop-in alternative)
