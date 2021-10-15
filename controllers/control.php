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

    public function _pattern($level = '[1-6]')
    {
        return "/<\/?h$level(.*)>(.*)</i";
    }

    public function find_and_replace($content)
    {
        if (!current_user_can('administrator')) {
            return $content;
        }

        $count = 1;
        $h1_count = $h2_count = $h3_count = $h4_count = $h5_count = $h6_count = 0;
        $toc = array();

        if (preg_match_all($this->_pattern(), $content, $header)) {
            $h = 0;
            foreach ($header[0] as $htag) {
                if (!preg_match('/name=/i', $htag)) {
                    for($i = 1; $i <= 6; $i++) {
                        if (preg_match($this->_pattern($i), $htag)) {
                            $var = 'h'.$i.'_count';
                            $$var++;
                            $id = 'f13-toc';
                            $keys = array();
                            for ($j = 1; $j < $i; $j++) {
                                $var = 'h'.$j.'_count';
                                $id .= '-'.$$var;
                                $keys[] = $$var;
                                $keys[] = 'children';
                            }
                            $var = 'h'.$i.'_count';
                            $id .= '-'.$$var;
                            for ($k = 6; $k > $i; $k--) {
                                $var = 'h'.$k.'_count';
                                $$var = 1;
                            }

                            $content = str_replace($htag, '<h'.$i.' id="'.$id.'" '.$header[1][$h].'>'.$header[2][$h].'<', $content);
                            switch ($i) {
                                case '1':
                                    $toc[$h1_count] = array('title' => $header[2][$h],'children' => array(),'level' => 'h1','anchor' => $id,);
                                    break;
                                case '2':
                                    $toc[$h1_count]['children'][$h2_count] = array('title' => $header[2][$h],'children' => array(),'level' => 'h2','anchor' => $id,);
                                    break;
                                case '3':
                                    $toc[$h1_count]['children'][$h2_count]['children'][$h3_count] = array('title' => $header[2][$h],'children' => array(),'level' => 'h3','anchor' => $id,);
                                    break;
                                case '4':
                                    $toc[$h1_count]['children'][$h2_count]['children'][$h3_count]['children'][$h4_count] = array('title' => $header[2][$h],'children' => array(),'level' => 'h4','anchor' => $id,);
                                    break;
                                case '5':
                                    $toc[$h1_count]['children'][$h2_count]['children'][$h3_count]['children'][$h4_count]['children'][$h5_count] = array('title' => $header[2][$h],'children' => array(),'level' => 'h5','anchor' => $id,);
                                    break;
                                case '6';
                                    $toc[$h1_count]['children'][$h2_count]['children'][$h3_count]['children'][$h4_count]['children'][$h5_count]['children'][$h6_count] = array('title' => $header[2][$h],'children' => array(),'level' => 'h6','anchor' => $id,);
                                    break;
                            }
                            $count++;
                        }
                    }
                }
                $h++;
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

}