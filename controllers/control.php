<?php namespace F13\TOC\Controllers;

class Control
{
    public function __construct()
    {
        add_filter('the_content', array($this, 'find_and_replace'), 100);
    }

    public function _generate_list($toc, $v = '')
    {
        $parent = false;
        foreach ($toc as $key => $item) {
            if (array_key_exists('title', $item)) {
                $v .= '<li><a href="#'.$item['anchor'].'">'.$item['title'].'</a>';
                    if (array_key_exists('children', $item)) {
                        $v .= $this->_generate_list($item['children']);
                    }
                $v .= '</li>';
                $parent = true;
            } else
            if (array_key_exists('children', $item)) {
                $v .= $this->_generate_list($item['children']);
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
        if (is_category()) {
            return $content;
        }
        if (strpos($content, '<!-- no-f13-toc -->') !== false) {
            return $content;
        }
        $count = 1;
        $h1_count = $h2_count = $h3_count = $h4_count = $h5_count = $h6_count = 0;
        $toc = array();

        if (preg_match_all($this->_pattern(), $content, $ht)) {
            $h = 0;
            foreach ($ht[0] as $htag) {
                for($i = 1; $i <= 6; $i++) {
                    if (preg_match($this->_pattern($i), $htag)) {
                        // Incremement current hx_count
                        $var = 'h'.$i.'_count';
                        $$var++;
                        // Set higher hx_count tags back to 0
                        for ($k = 6; $k > $i; $k--) {
                            $var = 'h'.$k.'_count';
                            $$var = 0;
                        }
                        // Check if h tag has an ID set
                        if (preg_match('/id="(.*)"/i', $htag, $tag)) {
                            // Use pre existing ID
                            $id = $tag[1];
                        } else {
                            // Generate an ID from the header stack
                            $id = 'f13-toc';
                            for ($j = 1; $j <= $i; $j++) {
                                $var = 'h'.$j.'_count';
                                $id .= '-'.$$var;
                            }
                            // Replace h tag to include new ID
                            $content = str_replace(
                                $htag,
                                '<h'.$i.' id="'.$id.'" '.$ht[1][$h].'>'.$ht[2][$h].'<',
                                $content
                            );
                        }
                        // Build an array of h tags for generating TOC
                        switch ($i) {
                            case '1': // h1
                                $toc[$h1_count] = array(
                                        'title' => $ht[2][$h],
                                        'children' => array(),
                                        'level' => 'h1',
                                        'anchor' => $id,
                                    );
                                break;
                            case '2': // h2
                                $toc[$h1_count]['children']
                                    [$h2_count] = array(
                                        'title' => $ht[2][$h],
                                        'children' => array(),
                                        'level' => 'h2',
                                        'anchor' => $id,
                                    );
                                break;
                            case '3': // h3
                                $toc[$h1_count]['children']
                                    [$h2_count]['children']
                                    [$h3_count] = array(
                                        'title' => $ht[2][$h],
                                        'children' => array(),
                                        'level' => 'h3',
                                        'anchor' => $id,
                                    );
                                break;
                            case '4': // h4
                                $toc[$h1_count]['children']
                                    [$h2_count]['children']
                                    [$h3_count]['children']
                                    [$h4_count] = array(
                                        'title' => $ht[2][$h],
                                        'children' => array(),
                                        'level' => 'h4'
                                        ,'anchor' => $id,
                                    );
                                break;
                            case '5': // h5
                                $toc[$h1_count]['children']
                                    [$h2_count]['children']
                                    [$h3_count]['children']
                                    [$h4_count]['children']
                                    [$h5_count] = array(
                                        'title' => $ht[2][$h],
                                        'children' => array(),
                                        'level' => 'h5',
                                        'anchor' => $id,
                                    );
                                break;
                            case '6'; // h6
                                $toc[$h1_count]['children']
                                    [$h2_count]['children']
                                    [$h3_count]['children']
                                    [$h4_count]['children']
                                    [$h5_count]['children']
                                    [$h6_count] = array(
                                        'title' => $ht[2][$h],
                                        'children' => array(),
                                        'level' => 'h6',
                                        'anchor' => $id,
                                    );
                                break;
                        }
                        $count++;
                    }
                }
                $h++;
            }
        }

        $v = '';
        // Check if toc contains items
        if (!empty($toc)) {
            // Container
            $v .= '<details class="f13-toc"role="navigation" aria-label="Navigate to sections of this page">';
                $v .= '<summary>'.__('Table of contents', 'f13-toc').'</summary>';
                // Recursive generator
                $v .= $this->_generate_list($toc);
            $v .= '</details>';
        }

        return $v.$content;
    }

}