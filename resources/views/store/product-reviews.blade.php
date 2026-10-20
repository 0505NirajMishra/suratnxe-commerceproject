<section class="sec product-reviews" id="reviews">
    <div class="product-review-summary">
        <h2>Ratings &amp; reviews</h2>
        <p>
            <span class="product-review-score"><span aria-hidden="true">★</span> {{ number_format((float) $product->rating, 1) }} / 5</span>
            <span class="product-review-count">{{ $product->reviews_count }} {{ \Illuminate\Support\Str::plural('review', $product->reviews_count) }}</span>
        </p>
    </div>

    @if(session('success'))
        <p class="success-message">{{ session('success') }}</p>
    @endif

    @error('review')
        <p class="error-message" role="alert">{{ $message }}</p>
    @enderror

    @if($canReview)
        <form method="post" action="{{ route('reviews.store', $product) }}" class="form-card product-review-form">
            @csrf
            <h3>Write a review</h3>
            <div class="field">
                <label for="review-rating">Your rating</label>
                <select id="review-rating" name="rating" required>
                    <option value="">Choose a rating</option>
                    @foreach(range(5, 1) as $rating)
                        <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5</option>
                    @endforeach
                </select>
                @error('rating')<small>{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="review-comment">Comment (optional)</label>
                <textarea id="review-comment" name="comment" rows="4" maxlength="2000">{{ old('comment') }}</textarea>
                @error('comment')<small>{{ $message }}</small>@enderror
            </div>
            <button class="btn btn-dark" type="submit">Submit review</button>
        </form>
    @elseif(auth()->check() && !auth()->user()->isAdmin())
        <p class="product-review-notice">
            @if($alreadyReviewed)
                You have already reviewed this fabric.
            @else
                Once your order is marked delivered, open <a href="{{ route('account') }}#orders">My Account → My Orders</a> and select “Rate this product”. The review form will also become available here.
            @endif
        </p>
    @elseif(!auth()->check())
        <p class="product-review-notice"><a href="{{ route('login') }}">Sign in</a> to review this fabric after delivery. The review form appears on this page once your order is delivered.</p>
    @endif

    <div class="product-review-list">
        <h3>Customer reviews</h3>
        @forelse($reviews as $review)
            <article class="product-review-card">
                <div class="product-review-card-head">
                    <span class="product-review-stars" aria-label="{{ $review->rating }} out of 5">
                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                    </span>
                    <strong>{{ $review->user->name }}</strong>
                </div>
                <p class="product-review-date">{{ $review->created_at->format('d M Y') }}</p>
                @if($review->comment)
                    <p class="product-review-comment">{{ $review->comment }}</p>
                @endif
            </article>
        @empty
            <p class="product-review-empty">No reviews yet for this product. Be the first to share your feedback.</p>
        @endforelse
        @if($reviews->hasPages())
            <div class="product-review-pagination">{{ $reviews->fragment('reviews')->links() }}</div>
        @endif
    </div>
</section>
