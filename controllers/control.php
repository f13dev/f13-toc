<?php namespace F13\TOC\Controllers;

class Control
{
    public function __construct()
    {
        add_filter('the_content', array($this, 'find_and_replace'), 100);
    }

    public function _extract_text($string)
    {
        $string = explode($string, '>');
        $string = $string[1];
        $string = explode($string, '<');
        $string = $string [0];

        return $string;
    }

    public function find_and_replace($content)
    {
        if (!current_user_can('administrator')) {
            return $content;
        }
        global $post;
        $pattern_all = "/<\/?h[1-3](.*)>(.*)</i";
        $pattern_h1 = "/<\/?h1(.*)>(.*)</i";
        $pattern_h2 = "/<\/?h2(.*)>(.*)</i";
        $pattern_h3 = "/<\/?h3(.*)>(.*)</i";
        $count = 1;
        $h1_count = 0;
        $h2_count = 0;
        $h3_count = 0;

        $toc = array();

        if (preg_match_all($pattern_all, $content, $header)) {
            $i = 0;
            foreach ($header[0] as $htag) {
                if (!preg_match('/name=/i', $htag)) {
                    if (preg_match($pattern_h1, $htag)) {
                        $h1_count++;
                        $h2_count = 1;
                        $h3_count = 1;
                        $content = str_replace($htag, '<h1 id="f13-toc-'.$h1_count.'" '.$header[1][$i].'>'.$header[2][$i].'<', $content);
                        $toc[$h1_count] = array(
                            'title' => $header[2][$i],
                            'children' => array(),
                            'level' => 'h1',
                            'anchor' => 'f13-toc-'.$h1_count,
                        );
                    } else
                    if (preg_match($pattern_h2, $htag)) {
                        $h2_count++;
                        $h3_count = 1;
                        $content = str_replace($htag, '<h2 id="f13-toc-'.$h1_count.'-'.$h2_count.'" '.$header[1][$i].'>'.$header[2][$i].'<', $content);
                        $toc[$h1_count]['children'][$h2_count] = array(
                            'title' => $header[2][$i],
                            'children' => array(),
                            'level' => 'h2',
                            'anchor' => 'f13-toc-'.$h1_count.'-'.$h2_count,
                        );
                    } else
                    if (preg_match($pattern_h3, $htag)) {
                        $h3_count++;
                        $content = str_replace($htag, '<h3 id="f13-toc-'.$h1_count.'-'.$h2_count.'-'.$h3_count.'" '.$header[1][$i].'>'.$header[2][$i].'<', $content);
                        $toc[$h1_count]['children'][$h2_count]['children'][$h3_count] = array(
                            'title' => $header[2][$i],
                            'level' => 'h3',
                            'anchor' => 'f13-toc-'.$h1_count.'-'.$h2_count.'-'.$h3_count,
                        );
                    }
                    $count++;
                }
                $i++;
            }
        }

        $v = '';
        if (!empty($toc)) {
            $v .= '<div class="f13-toc">';
                $v .= '<strong>'.__('Table of contents', 'f13-toc').'</strong>';
                $v .= $this->_generate_list($toc);
            $v .= '</div>';

            $v = '<details class="f13-toc">';
                $v .= '<summary>'.__('Table of contents', 'f13-toc').'</summary>';
                $v .= $this->_generate_list($toc);
            $v .= '</details>';
        }

        return $v.$content;

    }

    public function _generate_list($toc, $v = '')
    {
        foreach ($toc as $key => $item) {
            $parent = false;
            if (array_key_exists('title', $item)) {
                $v .= '<li><a href="#'.$item['anchor'].'">'.$item['title'].'</a>';
                    if (array_key_exists('children', $item)) {
                        $v .= $this->_generate_list($item['children']);
                    }
                $v .= '</li>';
                $parent = true;
            } else {
                if (array_key_exists('children', $item)) {
                    $v .= $this->_generate_list($item['children']);
                }
            }
        }
        return ($parent) ? '<ol>'.$v.'</ol>' : $v;
    }
}