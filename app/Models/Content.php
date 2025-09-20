<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'product_name',
        'target_audience', 
        'tone',
        'product_category',
        'generated_content',
        'word_count',
        'char_count'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    public function scopeByTone($query, $tone)
    {
        return $query->where('tone', $tone);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Accessors
    public function getContentTypeNameAttribute()
    {
        $types = [
            'instagram_caption' => 'Instagram Caption',
            'facebook_post' => 'Facebook Post',
            'email_subject' => 'Email Subject',
            'product_description' => 'Product Description'
        ];

        return $types[$this->content_type] ?? $this->content_type;
    }

    public function getToneNameAttribute()
    {
        $tones = [
            'casual' => 'Santai & Friendly',
            'professional' => 'Profesional',
            'friendly' => 'Ramah & Personal'
        ];

        return $tones[$this->tone] ?? $this->tone;
    }

    public function getProductCategoryNameAttribute()
    {
        $categories = [
            'elektronik' => 'Elektronik & Gadget',
            'fashion' => 'Fashion & Style',
            'makanan' => 'Food & Beverage', 
            'kosmetik' => 'Beauty & Cosmetics',
            'default' => 'Lainnya'
        ];

        return $categories[$this->product_category] ?? 'Lainnya';
    }

    public function getCreatedAtHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getPreviewContentAttribute()
    {
        return strlen($this->generated_content) > 100 
            ? substr($this->generated_content, 0, 97) . '...' 
            : $this->generated_content;
    }

    // Methods
    public function getReadingTime()
    {
        // Average reading speed: 200 words per minute
        $minutes = ceil($this->word_count / 200);
        return max(1, $minutes);
    }

    public function getSocialMediaOptimization()
    {
        $optimization = [];
        
        switch ($this->content_type) {
            case 'instagram_caption':
                $optimization['optimal_length'] = $this->char_count <= 150;
                $optimization['has_hashtags'] = strpos($this->generated_content, '#') !== false;
                $optimization['has_emoji'] = preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $this->generated_content);
                break;
                
            case 'facebook_post':
                $optimization['optimal_length'] = $this->char_count >= 100 && $this->char_count <= 500;
                $optimization['has_engagement'] = strpos(strtolower($this->generated_content), 'comment') !== false || strpos(strtolower($this->generated_content), 'share') !== false;
                break;
                
            case 'email_subject':
                $optimization['optimal_length'] = $this->char_count <= 50;
                $optimization['no_spam_words'] = !preg_match('/\b(free|urgent|buy now|limited time|act now)\b/i', $this->generated_content);
                break;
        }
        
        return $optimization;
    }
}