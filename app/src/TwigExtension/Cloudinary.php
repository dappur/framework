<?php

namespace Dappur\TwigExtension;

/**
 * Cloudinary twig extension.
 *
 * adapted from @author Stefan Gotre <gotre@teraone.de>
 */
class Cloudinary extends \Twig_Extension
{
    public function getName()
    {
        return 'cloudinary';
    }

    public function getFunctions()
    {
        return array(
            'clUploadUrl' => new \Twig_SimpleFunction(
                'clUploadUrl',
                [$this, 'clUploadUrl']
            ),
            'clImageTag' => new \Twig_SimpleFunction(
                'clImageTag',
                [$this, 'clImageTag'],
                array('is_safe' => array('html'))
            ),
            'cloudinaryJsConfig' => new \Twig_SimpleFunction(
                'cloudinaryJsConfig',
                [$this, 'cloudinaryJsConfig'],
                array('is_safe' => array('html', 'js'))
            ),
            'clUrl' => new \Twig_SimpleFunction(
                'clUrl',
                [$this, 'clUrl']
            ),
            'clVideoPath' => new \Twig_SimpleFunction(
                'clVideoPath',
                [$this, 'clVideoPath']
            ),
            'clVideoThumb' => new \Twig_SimpleFunction(
                'clVideoThumb',
                [$this, 'clVideoThumb']
            ),
            'clVideoTag' => new \Twig_SimpleFunction(
                'clVideoTag',
                [$this, 'clVideoTag'],
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('tinymce_init', [$this, 'tinymceInit'], array('is_safe' => array('html')))
        );
    }

    public function clUploadUrl($options = array())
    {
        return cl_upload_url($options);
    }

    public function clImageTag($source, $options = array())
    {
        return cl_image_tag($source, $options);
    }

    public function cloudinaryJsConfig()
    {
        return cloudinary_js_config();
    }

    public function clUrl($source, $options = array())
    {
        return cloudinary_url($source, $options);
    }

    public function clVideoPath($source, $options = array())
    {
        return cl_video_path($source, $options = array());
    }

    public function clVideoThumb($source, $options = array())
    {
        return cl_video_thumbnail_path($source, $options);
    }

    public function clVideoTag($source, $options = array())
    {
        return cl_video_tag($source, $options);
    }
}
