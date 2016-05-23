<?php

namespace app\models;

class Feed extends \yii\db\ActiveRecord {

    public static function tableName() {
        return 'feeds';
    }

    public function getUrl() {
        $url = '/momenti/web/feed/';
        /*
        $chars = str_split($this->name);
        $lastIsHyphen = false;
        foreach ($chars as $char) {
            $a = ctype_alnum($char);
            if ($a) {
                $url .= $char;
                $lastIsHyphen = false;
            } else if (!$lastIsHyphen) {
                $url .= '-';
                $lastIsHyphen = true;
            }
        }
        */
        $url .= $this->id;

        return $url;
    }

    public function printLink($text = null) {
        if ($text == null) {
            $text = ucwords($this->name);
        }

        $url = $this->getUrl();

        echo "<a href=\"$url\">$text</a>";
    }

    public function findPosts($page=0, $order='created_source', $direc='DESC', $filters=[]) {
        $offset = $page*12;
        $feedSql = $this->feedSql($filters);
        $query = Post::find()->where($feedSql);
                
        return $query->orderBy("$order $direc")->limit(12)->offset($offset)->all();
    }
    
    public function postCount($filters) {
        $feedSql = $this->feedSql($filters);
        $query = Post::find()->where($feedSql);
        
        return $query->count();
    }
    
    public function feedSql($filters=[]) {
        $_params = json_decode($this->params);
        
        $networks = explode(",", $this->network);
        $tags = $_params->tags;
        $authors = $_params->authors;
        
        $quottedNetworks = [];
        foreach($networks as $network) {
            $quottedNetworks[] = "'$network'";
        }
        $_networks = implode(",", $quottedNetworks);
        
        $quottedAuthors = [];
        foreach($authors as $author) {
            $quottedAuthors[] = "'$author'";
        }
        $_authors = implode(",", $quottedAuthors);
        $tagsQuery = [];
        foreach ($tags as $tag) {
            $tagsQuery[] = "posts.tags LIKE '%$tag%'";
        }
        $_tags = implode(" OR ", $tagsQuery);
        
        $sql = "(posts.source IN ($_networks)) AND (posts.author IN ($_authors) OR ($_tags))";
        
        if (count($filters) > 0) {
            if (isset($filters['media_type']) && $filters['media_type'] != null) {
                $arr = explode(',', $filters['media_type']);
                $arr2 = [];
                foreach ($arr as $a) {
                    $arr2[] = "'".addslashes($a)."'";
                }
                $str = implode(',', $arr2);
                $sql .= " AND (media_type IN ($str))";
            }
            if (isset($filters['remove_tags']) && $filters['remove_tags'] != null) {
                $notTagsQuery = [];
                foreach (explode(',', $filters['remove_tags']) as $tag) {
                    $notTagsQuery[] = "tags NOT LIKE '%$tag%'";
                }
                $_notTags = implode(" AND ", $notTagsQuery);
                $sql .= " AND ($_notTags)";
            }
            if (isset($filters['remove_authors']) && $filters['remove_authors'] != null) {
                $notQuottedAuthors = [];
                foreach(explode(',', $filters['remove_authors']) as $author) {
                    $notQuottedAuthors[] = "'$author'";
                }
                $_notAuthors = implode(",", $notQuottedAuthors);
                $sql .= " AND (posts.author NOT IN ($_notAuthors))";
            }
            if (isset($filters['author']) && $filters['author'] != null) {
                $sql .= "AND author = '$filters[author]'";
            }
        }
        return $sql;
    }
    
    public function getAuthorMetrics($a=null, $b=null) {
        $feedSql = $this->feedSql();
        $query = Author::find()->select("authors.*, COUNT(posts.id) as postCount")->join("INNER JOIN", 'posts', 'authors.author_id=posts.author_id')->where($feedSql);
        if ($a != null) {
            $query->andWhere("posts.created_source >= $a");
        }
        if ($b != null) {
            $query->andWhere("posts.created_source <= $b");
        }
        
        $authors = $query->orderBy("postCount DESC")->groupBy("authors.id")->all();
        $return = [
            'authors' => $authors,
            'count' => count($authors),
        ];
        
        return $return;
    }
    
    public function getMediaMetrics($a=null, $b=null) {
        $feedSql = $this->feedSql();
        $query = "SELECT COUNT(*) as count, media_type FROM posts WHERE $feedSql";
        if ($a != null) {
            $query .= " AND posts.created_source >= $a";
        }
        if ($b != null) {
            $query .= " AND posts.created_source <= $b";
        }
        $query .= " GROUP BY media_type";
        return \Yii::$app->db->createCommand($query)->queryAll();
    }
    
    public function getTagMetrics($a=null, $b=null) {
        $feedSql = $this->feedSql();
        $query = "SELECT COUNT(*) as count, tags FROM posts WHERE $feedSql";
        if ($a != null) {
            $query .= " AND posts.created_source >= $a";
        }
        if ($b != null) {
            $query .= " AND posts.created_source <= $b";
        }
        $query .= " GROUP BY tags";
        $results = \Yii::$app->db->createCommand($query)->queryAll();
        $return['groups'] = $results;
        $tags = [];
        foreach ($results as $result) {
            foreach (json_decode($result['tags']) as $tag_) {
                $tags[] = strtolower($tag_);
            }
        }
        sort($tags);
        $return['individuals'] = array_count_values($tags);
        
        return $return;
    }
    
    public function getSentimentMetrics($a=null, $b=null) {
        
    }
    
    public function getIntentMetrics($a=null, $b=null) {
        
    }
    
    public static function sort($posts) {
        $count = $posts;
        for ($i = 0; $i < $count - 1; $i++) {
            $minPos = $i;
            for ($j = $i + 1; $j < $count; $j++) {
                $num1 = strtotime($posts[$i]->created_source);
                $num2 = strtotime($posts[$j]->created_source);
                if ($num1 > $num2) {
                    $minPos = $j;
                }
            }
            $posts = $this->swap($posts, $minPos, $i);
        }
        return $posts;
    }

    public static function swap($posts, $a, $b) {
        $tmp = $posts[$a];
        $posts[$a] = $posts[$b];
        $posts[$b] = $tmp;
    }

}
