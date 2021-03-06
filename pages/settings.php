<?php

class settings extends base {
    
    public function submenu() {
        $submenu=array();
        $submenu[0]["index"]["link"]="submenu=settings";
        $submenu[0]["index"]["name"]="Settings";
        $submenu[1]["index"]["link"]="submenu=members";
        $submenu[1]["index"]["name"]="Members";
        $this->page_manager->submenu($submenu);
    }
    
    private function members() {
            
$urows=$this->db->get_rows("users");
$list="";
foreach ($urows as $urow) {
    $list.="<label class=\"label\"><a href='?page=settings&submenu=members&profid={$urow['id']}'>{$urow['username']}</a></label>\n";
}
$form_block=<<<FORMBLOCK
       <div class="block" id="block-forms">
           <div class="content">
            <h2 class="title">Users</h2>
            <div class="inner">
              <form action="#" method="get" class="form">
                <div class="group">
                  {$list}
                </div>
              </form>
            </div>
          </div>
        </div>
FORMBLOCK;
$this->content.=$form_block;
        }

public function profile_show_id($profid) {
    $id=(int)$profid;
    $find=array(
    "id"=>$id,
    );
    $urow=$this->db->get_row("users",$find);
$form_block=<<<FORMBLOCK
<form id='settings' name='settings' method=POST>
       <div class="block" id="block-forms">
           <div class="content">
            <h2 class="title">User Profile - id {$id}</h2>
            <div class="inner">
              <form action="#" method="get" class="form">
                <div class="group">
                  <label class="label">Username</label>
                  <input type="text" class="text_field" value="{$urow["username"]}"/>
                  <span class="description">Ex: a simple text</span>
                </div>
                <div class="group">
                  <div class="fieldWithErrors">
                    <label class="label" for="post_title">Password</label>
                    <span class="error">can't be blank</span>
                  </div>
                  <input type="text" class="text_field" value="{$urow["password"]}"/>
                  <span class="description">Ex: a simple text</span>
                </div>
                <div class="group">
                  <div class="fieldWithErrors">
                    <label class="label" for="post_title">Access right</label>
                    <span class="error">can't be blank</span>
                  </div>
                  <input type="text" class="text_field" value="{$urow["ulevel"]}"/>
                  <span class="description">Ex: a simple text</span>
                </div>
                <div class="group">
                  <label class="label">Text area</label>
                  <textarea class="text_area" rows="10" cols="80"></textarea>
                  <span class="description">Write here a long text</span>
                </div>
                <div class="group navform wat-cf">
                  <button class="button" type="submit">
                    <img src="images/icons/tick.png" alt="Save" /> Save
                  </button>
                  <span class="text_button_padding">or</span>
                  <a class="text_button_padding link_button" href="#header">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
FORMBLOCK;
$this->content.=$form_block;
    }

    private function format_storage($storage) {
            $format_array=array(0,1,2,3,4,5,6,7,8,9,"a","b","c","d","e","f");
            $path=$this->settings[$storage];
            $message="";
            foreach ($format_array as $fa1)
            foreach ($format_array as $fa2) {
                mkdir($path."/".$fa1."/".$fa2,0777, true) or $message.="Cannot create path {$path}/{$fa1}/{$fa2}<br>\n";
            }
            $message.="Finished creating directories!<br>\n";
            return "{$message}<br>\n";
        }

    private function addbutton($fieldname,$value) {
        if ($value>0)
            $image="tick.png"; else
            $image="cross.png";
        $button=$this->page_manager->button($fieldname,$fieldname,$image);
        return $button;
    }

    private function addfield($fieldname,$value,$info) {
        return "<td><strong>{$fieldname}</strong></td><td><input type='box' name='{$fieldname}' value='{$value}'>&nbsp{$info}</td>\n";
    }
    
    public function show_settings() {
        $ssettings=$this->settings;
        ksort($ssettings);
        $output.="<form id='settings' name='settings' method=POST>\n";
        $output.="<table>\n";
        foreach ($ssettings as $set=>$value) {
        $output.="<tr>\n";
           if (strstr($set,"storage")) {
                $total=disk_total_space($value);
                $free=disk_free_space($value);
                $perc=ROUND(($free*100)/$total);
                $totalr=number_readable($total);
                $freer=number_readable($free);
                //$output.="<td><strong>{$set} path</strong></td><td><em>{$value}</em>&nbsp[<strong>{$freer}</strong> of <strong>{$totalr}</strong>, {$perc}%]</td><td><a href='?page=settings&format_storage={$set}'><strong>Format storage</strong></a></td>\n";
                $info="[<strong>{$freer}</strong> of <strong>{$totalr}</strong>, {$perc}%]<a href='?page=settings&format_storage={$set}'>&nbsp<strong>Format storage</strong></a>";
                $output.=$this->addfield($set,$value,$info);
                } else
           if ($set=="admin_email") {
                $output.=$this->addfield($set,$value,"admin email to show on requests");
                } else
            if ($set=="debug") {
                $output.=$this->addfield($set,$value,"0=all, 1=warnings, 2=errors");
                }
        $output.="</tr>\n";
        }
        $output.="</table>\n";
        $output.="</br>\n";
        $output.="<input type=submit value=Change>\n";
        $output.="</form>\n";
        $this->content.=$output."<br />\n";
    }

    private function reload_settings() {
    // get settings from db
    $settings=$this->db->get_rows("settings");
    $storageno=1;
    foreach ($settings as $setting) {
        $set[$setting["setting"]]=$setting["value"];
        extract($set);
        // generating storage array
        if (strstr($setting["setting"],"storage")) {
            $storage[$storageno]=$setting["value"];
            $storageno++;
            }
        }
    $this->settings=$set;
    }
    
    private function save_new_settings($settings) {
        if (!$this->db)
            die("No DB!");
        unset($settings["page"]);
        unset($settings["submenu"]);
        if (empty($settins["debug"]))
            $settins["debug"]="0";
        
        $this->db->sql("TRUNCATE `settings`");
        foreach ($settings as $var=>$value) {
            unset($row);
            $row["setting"]=$var;
            $row["value"]=$value;
            if (!$this->db->insert_row("settings",$row))
                $this->content.="ERROR: ".$this->db->lastsql.$this->db->lasterror."<br>\n";
        }
        $this->reload_settings();
    }

    public function run() {
        $this->get_request();
        foreach ($this->requests as $request=>$value) {
            switch($request) {
                case "format_storage":
                    $message=$this->format_storage($value);
                break;
                default:
                if ($this->requests["debug"] || $this->requests["admin_email"] || $this->requests["storage1"])
                    $this->save_new_settings($this->requests);
            }
        }

        $submenu=$_REQUEST["submenu"];
        $profid=$_REQUEST["profid"];
        
        switch($submenu) {
            case "members":
            if ($profid)
            $this->profile_show_id($profid); else
            $this->members();
            break;
            case "settings":
            $this->show_settings();
            break;
            default:
            header("Location: index.php?page=settings&submenu=settings");
        }

        $this->submenu();
        $this->page_manager->finish_script($message.$this->content);

    }
    
}

?>