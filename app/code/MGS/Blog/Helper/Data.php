<?php

namespace MGS\Blog\Helper;

class Data extends \MGS\Mpanel\Helper\Data
{

    public function getConfig($key, $store = null)
    {
		return $this->getStoreConfig('blog/' . $key);
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getRoute()
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
        return $this->_storeManager->getStore()->getBaseUrl() . $route;
    }

    public function getTagUrl($tag)
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
        return $this->_storeManager->getStore()->getBaseUrl() . $route . '/tag/' . urlencode($tag);
    }

    public function convertSlashes($tag, $direction = 'back')
    {
        if ($direction == 'forward') {
            $tag = preg_replace("#/#is", "&#47;", $tag);
            $tag = preg_replace("#\\\#is", "&#92;", $tag);
            return $tag;
        }
        $tag = str_replace("&#47;", "/", $tag);
        $tag = str_replace("&#92;", "\\", $tag);
        return $tag;
    }

    public function checkLoggedIn()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn();
    }
	
    public function getImageThumbnailPost($post)
    {	
		$imageUrl = "";
        $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        
		if($post->getVideoThumbId() != "" && $post->getThumbType() == "video"){
            if($post->getThumbnail() == ""){
                return $this->getThumbnailImgVideoPost($post);
            }else {
                $imageUrl = $mediaUrl . $post->getThumbnail();
            }
        }else {
            $imageUrl = $mediaUrl . $post->getThumbnail();
        }
        
        return $imageUrl;
    }
	
	public function getPostUrl($post) {
        
        $url = $post->getPostUrlWithNoCategory();
		
		return $url;
	}
	
    public function getImagePost($post)
    {	
		$imageUrl = "";
        $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        
		if($post->getVideoBigId() != "" && $post->getImageType() == "video"){
            if($post->getImageUrl() == ""){
                return $this->getThumbnailImgVideoPost($post);
            }else {
                $imageUrl = $post->getImageUrl();
            }
        }else {
            $imageUrl = $post->getImageUrl();
        }
        
        return $imageUrl;
    }
    
	public function getVideoThumbUrl($post)
    {	
        if($post->getVideoThumbType() == "youtube"){
            $video_url = 'https://www.youtube.com/watch?v='.$post->getVideoThumbId();
        }else {
            $video_url = 'https://vimeo.com/'.$post->getVideoThumbId();
        }
        
		return $video_url;
    }
    
	public function getVideoBigUrl($post)
    {	
        if($post->getVideoBigType() == "youtube"){
            $video_url = 'https://www.youtube.com/watch?v='.$post->getVideoBigId();
        }else {
            $video_url = 'https://vimeo.com/'.$post->getVideoBigId();
        }
        
		return $video_url;
    }
	
	
	public function getThumbnailImgVideoPost($post)
    {	
		if($post->getThumbType() == "video"){
			if($post->getVideoThumbId() != ""){
				if($post->getVideoThumbType() == "youtube"){
					return 'http://img.youtube.com/vi/'.$post->getVideoThumbId().'/hqdefault.jpg';
				}else {
					$info = 'thumbnail_medium';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'vimeo.com/api/v2/video/'.$post->getVideoThumbId().'.php');
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$output = unserialize(curl_exec($ch));
					$output = $output[0][$info];
					curl_close($ch);
					return $output;
				}
			}
			
		}
		return;
    }
	
}
