<?php

namespace Dappur\TwigExtension;

/**
 * Cloudinary twig extension.
 *
 * @author Stefan Gotre <gotre@teraone.de>
 */
class Cloudinary extends \Twig_Extension
{

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cloudinary';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'cl_upload_url'                          => new \Twig_SimpleFunction('cl_upload_url', [$this, 'cl_upload_url']),
            'cl_upload_tag_params'                   => new \Twig_SimpleFunction('cl_upload_tag_params', [$this, 'cl_upload_tag_params']),
            'cl_unsigned_image_upload_tag'           => new \Twig_SimpleFunction('cl_unsigned_image_upload_tag', [$this, 'cl_unsigned_image_upload_tag'], array('is_safe' => array('html'))),
            'cl_image_upload_tag'                    => new \Twig_SimpleFunction('cl_image_upload_tag', [$this, 'cl_image_upload_tag'], array('is_safe' => array('html'))),
            'cl_upload_tag'                          => new \Twig_SimpleFunction('cl_upload_tag', [$this, 'cl_upload_tag'],array('is_safe' => array('html'))),
            'cl_form_tag'                            => new \Twig_SimpleFunction('cl_form_tag', [$this, 'cl_form_tag'], array('is_safe' => array('html'))),
            'cl_image_tag'                           => new \Twig_SimpleFunction('cl_image_tag', [$this, 'cl_image_tag'], array('is_safe' => array('html'))),
            'fetch_image_tag'                        => new \Twig_SimpleFunction('fetch_image_tag', [$this, 'fetch_image_tag'], array('is_safe' => array('html'))),
            'facebook_profile_image_tag'             => new \Twig_SimpleFunction('facebook_profile_image_tag', [$this, 'facebook_profile_image_tag'], array('is_safe' => array('html'))),
            'gravatar_profile_image_tag'             => new \Twig_SimpleFunction('gravatar_profile_image_tag', [$this, 'gravatar_profile_image_tag'], array('is_safe' => array('html'))),
            'twitter_profile_image_tag'              => new \Twig_SimpleFunction('twitter_profile_image_tag', [$this, 'twitter_profile_image_tag'], array('is_safe' => array('html'))),
            'twitter_name_profile_image_tag'         => new \Twig_SimpleFunction('twitter_name_profile_image_tag', [$this, 'twitter_name_profile_image_tag'], array('is_safe' => array('html'))),
            'cloudinary_js_config'                   => new \Twig_SimpleFunction('cloudinary_js_config', [$this, 'cloudinary_js_config'], array('is_safe' => array('html', 'js'))),
            'cloudinary_url'                         => new \Twig_SimpleFunction('cloudinary_url', [$this, 'cloudinary_url']),
            'cl_sprite_url'                          => new \Twig_SimpleFunction('cl_sprite_url', [$this, 'cl_sprite_url']),
            'cl_sprite_tag'                          => new \Twig_SimpleFunction('cl_sprite_tag', [$this, 'cl_sprite_tag'], array('is_safe' => array('html'))),
            'cl_video_path'                          => new \Twig_SimpleFunction('cl_video_path', [$this, 'cl_video_path']),
            'cl_video_thumbnail_tag'                 => new \Twig_SimpleFunction('cl_video_thumbnail_tag', [$this, 'cl_video_thumbnail_tag'], array('is_safe' => array('html'))),
            'cl_video_thumbnail_path'                 => new \Twig_SimpleFunction('cl_video_thumbnail_path', [$this, 'cl_video_thumbnail_path']),
            'cl_video_tag'                           => new \Twig_SimpleFunction('cl_video_tag', [$this, 'cl_video_tag'], array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('tinymce_init', [$this, 'tinymceInit'], array('is_safe' => array('html')))



        );
    }

    /**
     * @param  array  $options
     * @return string
     */
    public function cl_upload_url($options = array())
    {
       return cl_upload_url($options);
    }

    /**
     * @param  array  $options
     * @return string
     */
    public function cl_upload_tag_params($options = array())
    {
        return cl_upload_tag_params($options = array());
    }

    /**
     * @param $field
     * @param $upload_preset
     * @param array $options
     */
    public function cl_unsigned_image_upload_tag($field, $upload_preset, $options = array())
    {
        cl_unsigned_image_upload_tag($field, $upload_preset, $options);
    }

    /**
     * @param $field
     * @param  array  $options
     * @return string
     */
    public function cl_image_upload_tag($field, $options = array())
    {
        return cl_image_upload_tag($field, $options);
    }

    /**
     * @param $field
     * @param  array  $options
     * @return string
     */
    public function cl_upload_tag($field, $options = array())
    {
        return  cl_upload_tag($field, $options);
    }

    /**
     * @param $callback_url
     * @param  array  $options
     * @return string
     */
    public function cl_form_tag($callback_url, $options = array())
    {
        return cl_form_tag($callback_url, $options);
    }

    /**
     * @param $source
     * @param  array  $options
     * @return string
     */
    public function cl_image_tag($source, $options = array())
    {
        return cl_image_tag($source, $options);
    }

    /**
     * @param $url
     * @param  array  $options
     * @return string
     */
    public function fetch_image_tag($url, $options = array())
    {
        return fetch_image_tag($url, $options);
    }

    /**
     * @param $profile
     * @param  array  $options
     * @return string
     */
    public function facebook_profile_image_tag($profile, $options = array())
    {
        return facebook_profile_image_tag($profile, $options);
    }

    /**
     * @param $email
     * @param  array  $options
     * @return string
     */
    public function gravatar_profile_image_tag($email, $options = array())
    {
        return gravatar_profile_image_tag($email, $options);
    }

    /**
     * @param $profile
     * @param  array  $options
     * @return string
     */
    public function twitter_profile_image_tag($profile, $options = array())
    {
        return twitter_profile_image_tag($profile, $options);
    }

    /**
     * @param $profile
     * @param  array  $options
     * @return string
     */
    public function twitter_name_profile_image_tag($profile, $options = array())
    {
         return twitter_name_profile_image_tag($profile, $options);
    }

    /**
     * @return string
     */
    public function cloudinary_js_config()
    {
        return cloudinary_js_config();
    }

    /**
     * @param $source
     * @param  array $options
     * @return mixed
     */
    public function cloudinary_url($source, $options = array())
    {
        return cloudinary_url($source, $options);
    }

    /**
     * @param $tag
     * @param  array $options
     * @return mixed
     */
    public function cl_sprite_url($tag, $options = array())
    {
        return cl_sprite_url($tag, $options);
    }

    /**
     * @param $tag
     * @param  array  $options
     * @return string
     */
    public function cl_sprite_tag($tag, $options = array())
    {
        return cl_sprite_tag($tag, $options);
    }

    /**
     * @param $source
     * @param  array $options
     * @return mixed
     */
    public function cl_video_path($source, $options = array())
    {
        return cl_video_path($source, $options = array());
    }

    /**
     * @param $source
     * @param  array  $options
     * @return string
     */
    public function cl_video_thumbnail_tag($source, $options = array())
    {
        return cl_video_thumbnail_tag($source, $options);
    }

    public function cl_video_thumbnail_path($source, $options = array())
    {
        return cl_video_thumbnail_path($source, $options);
    }

    /**
     * @param $source
     * @param  array  $options
     * @return string
     */
    public function cl_video_tag ($source, $options = array())
    {
        return cl_video_tag($source, $options);
    }
}
