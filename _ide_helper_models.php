<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string|null $title
 * @property string $image_path
 * @property string|null $link
 * @property string|null $description
 * @property int $order
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereUpdatedAt($value)
 */
	class Banner extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $parent_id
 * @property string|null $icon
 * @property int $is_active
 * @property int $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CategoryAttribute> $attributes
 * @property-read int|null $attributes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostImage> $images
 * @property-read int|null $images_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $posts
 * @property-read int|null $posts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $key
 * @property string $type
 * @property array<array-key, mixed>|null $options
 * @property bool $is_required
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryAttribute whereUpdatedAt($value)
 */
	class CategoryAttribute extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $chatbot_session_id
 * @property string $role
 * @property string $content
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChatbotSession $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereChatbotSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereUpdatedAt($value)
 */
	class ChatbotMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatbotMessage> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereUserId($value)
 */
	class ChatbotSession extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $buyer_id
 * @property int $seller_id
 * @property int|null $post_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $buyer
 * @property-read \App\Models\Message|null $latestMessage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\Post|null $post
 * @property-read \App\Models\User $seller
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereUpdatedAt($value)
 */
	class Conversation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property string|null $message_text
 * @property string|null $image_path
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $post_id
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\Post|null $post
 * @property-read \App\Models\User $sender
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMessageText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property int $buyer_id
 * @property int $seller_id
 * @property string|null $shipping_name
 * @property string|null $shipping_phone
 * @property string|null $shipping_address
 * @property string|null $shipping_province_id
 * @property string|null $shipping_ward_id
 * @property string|null $shipping_note
 * @property numeric|null $total_price
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $buyer
 * @property-read \App\Models\Post $post
 * @property-read \App\Models\Review|null $review
 * @property-read \App\Models\User $seller
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingWardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property numeric $price
 * @property string|null $address
 * @property int|null $province_id
 * @property string|null $province_name
 * @property int|null $ward_id
 * @property string|null $ward_name
 * @property string $phone
 * @property array<array-key, mixed>|null $specifications
 * @property string $status
 * @property string|null $reject_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $favoritedBy
 * @property-read int|null $favorited_by_count
 * @property-read mixed $is_favorited
 * @property-read mixed $is_ordered
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereProvinceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereRejectReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereWardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereWardName($value)
 */
	class Post extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property string $image_path
 * @property int $is_primary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereUpdatedAt($value)
 */
	class PostImage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereUserId($value)
 */
	class RefreshToken extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $reviewer_id
 * @property int $reviewed_user_id
 * @property int $rating
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\User $reviewedUser
 * @property-read \App\Models\User $reviewer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUpdatedAt($value)
 */
	class Review extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $avatar
 * @property string|null $address
 * @property int|null $province_id
 * @property string|null $province_name
 * @property int|null $ward_id
 * @property string|null $ward_name
 * @property int $role 0: User, 1: Admin
 * @property int $status 1: Active, 0: Locked
 * @property numeric $rating
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $buyerConversations
 * @property-read int|null $buyer_conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $favoritePosts
 * @property-read int|null $favorite_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followings
 * @property-read int|null $followings_count
 * @property-read mixed $average_rating
 * @property-read mixed $reviews_count
 * @property-read mixed $sold_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $givenReviews
 * @property-read int|null $given_reviews_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $ordersAsBuyer
 * @property-read int|null $orders_as_buyer_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $ordersAsSeller
 * @property-read int|null $orders_as_seller_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $posts
 * @property-read int|null $posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $receivedReviews
 * @property-read int|null $received_reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $sellerConversations
 * @property-read int|null $seller_conversations_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProvinceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWardName($value)
 */
	class User extends \Eloquent implements \Tymon\JWTAuth\Contracts\JWTSubject {}
}

