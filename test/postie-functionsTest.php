<?php

require 'wpstub.php';
require'../postie-functions.php';
require'../simple_html_dom.php';
require '../postie.php';

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('POSTIE_DEBUG', true);

class postiefunctionsTest extends PHPUnit_Framework_TestCase {

    public function testAllowCommentsOnPost() {
        $original_content = "test content, no comment control";
        $modified_content = "test content, no comment control";
        $allow = tag_AllowCommentsOnPost($modified_content);
        $this->assertEquals("open", $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control closed ";
        $modified_content = "test content, comment control closed comments:0";
        $allow = tag_AllowCommentsOnPost($modified_content);
        $this->assertEquals("closed", $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control open ";
        $modified_content = "test content, comment control open comments:1";
        $allow = tag_AllowCommentsOnPost($modified_content);
        $this->assertEquals("open", $allow);
        $this->assertEquals($original_content, $modified_content);

        $original_content = "test content, comment control registered only ";
        $modified_content = "test content, comment control registered only comments:2";
        $allow = tag_AllowCommentsOnPost($modified_content);
        $this->assertEquals("registered_only", $allow);
        $this->assertEquals($original_content, $modified_content);
    }

    public function testBannedFileName() {
        $this->assertFalse(isBannedFileName("", null));
        $this->assertFalse(isBannedFileName("", ""));
        $this->assertFalse(isBannedFileName("", array()));
        $this->assertFalse(isBannedFileName("test", array()));
        $this->assertTrue(isBannedFileName("test", array("test")));
        $this->assertFalse(isBannedFileName("test", array("test1")));
        $this->assertTrue(isBannedFileName("test.exe", array("*.exe")));
        $this->assertFalse(isBannedFileName("test.pdf", array("*.exe")));
        $this->assertFalse(isBannedFileName("test.pdf", array("*.exe", "*.js", "*.cmd")));
        $this->assertFalse(isBannedFileName("test.cmd.pdf", array("*.exe", "*.js", "*.cmd")));
        $this->assertTrue(isBannedFileName("test test.exe", array("*.exe")));
    }

    public function testCheckEmailAddress() {
        $this->assertFalse(isEmailAddressAuthorized(null, null));
        $this->assertFalse(isEmailAddressAuthorized(null, array()));
        $this->assertFalse(isEmailAddressAuthorized("", array()));
        $this->assertFalse(isEmailAddressAuthorized("", array("")));
        $this->assertFalse(isEmailAddressAuthorized("bob", array("jane")));
        $this->assertTrue(isEmailAddressAuthorized("bob", array("bob")));
        $this->assertTrue(isEmailAddressAuthorized("bob", array("BoB")));
        $this->assertTrue(isEmailAddressAuthorized("bob", array("bob", "jane")));
        $this->assertTrue(isEmailAddressAuthorized("bob", array("jane", "bob")));
    }

    public function testConvertUTF8ToISO_8859_1() {
        $this->assertEquals("test", ConvertUTF8ToISO_8859_1("random", "stuff", "test"));
        $this->assertEquals("Phasa Thai", ConvertUTF8ToISO_8859_1('quoted-printable', 'iso-8859-1', "Phasa Thai"));
        $this->assertEquals("ภาษาไทย Phasa Thai", ConvertUTF8ToISO_8859_1('quoted-printable', 'tis-620', "=C0=D2=C9=D2=E4=B7=C2 Phasa Thai"));
        $this->assertEquals("??????? Phasa Thai", ConvertUTF8ToISO_8859_1('base64', 'utf-8', "ภาษาไทย Phasa Thai"));
        $this->assertEquals("ภาษาไทย Phasa Thai", ConvertUTF8ToISO_8859_1('something', 'utf-8', "ภาษาไทย Phasa Thai"));
        $this->assertEquals("ภาษาไทย Phasa Thai", ConvertUTF8ToISO_8859_1('base64', 'iso-8859-1', "ภาษาไทย Phasa Thai"));
    }

    public function testConvertToUTF_8() {
        $this->assertEquals("に投稿できる", ConvertToUTF_8('iso-2022-jp', iconv("UTF-8", "ISO-2022-JP", "に投稿できる")));
        $this->assertEquals("Код Обмена Информацией, 8 бит", ConvertToUTF_8('koi8-r', iconv("UTF-8", "koi8-r", "Код Обмена Информацией, 8 бит")));
    }

    public function testDeterminePostDate() {
        $content = "test";
        $r = DeterminePostDate($content);
        $this->assertTrue(is_array($r));
        $this->assertEquals(3, count($r));
        $this->assertEquals(0, $r[2]);
        $this->assertEquals("test", $content);

        $content = "test delay:";
        $r = DeterminePostDate($content);
        $this->assertEquals(0, $r[2]);
        $this->assertEquals("test delay:", $content);

        $content = "test delay:1h";
        $r = DeterminePostDate($content);
        $this->assertEquals(3600, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:1d";
        $r = DeterminePostDate($content);
        $this->assertEquals(86400, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:1m";
        $r = DeterminePostDate($content);
        $this->assertEquals(60, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:m";
        $r = DeterminePostDate($content);
        $this->assertEquals(0, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:dhm";
        $r = DeterminePostDate($content);
        $this->assertEquals(0, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:x";
        $r = DeterminePostDate($content);
        $this->assertEquals(0, $r[2]);
        $this->assertEquals("test delay:x", $content);

        $content = "test delay:-1m";
        $r = DeterminePostDate($content);
        $this->assertEquals(-60, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:1d1h1m";
        $r = DeterminePostDate($content);
        $this->assertEquals(90060, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test delay:d1hm";
        $r = DeterminePostDate($content);
        $this->assertEquals(3600, $r[2]);
        $this->assertEquals("test ", $content);

        $content = "test";
        $r = DeterminePostDate($content, '2012-11-20 08:00', 1);
        $this->assertEquals('2012-11-20 17:00:00', $r[0]);
        $this->assertEquals(0, $r[2]);
        $this->assertEquals("test", $content);
    }

    public function testEndFilter() {
        $c = "test";
        $this->assertEquals("test", filter_end($c, "xxx"));

        $c = "test xxx";
        $this->assertEquals("test ", filter_end($c, "xxx"));

        $c = "test xxx test";
        $this->assertEquals("test ", filter_end($c, "xxx"));

        $c = "tags: Station, Kohnen, Flugzeug\n:end\n21.10.2012";
        $this->assertEquals("tags: Station, Kohnen, Flugzeug\n", filter_end($c, ":end"));

        $c = "This is a test :end";
        $this->assertEquals("This is a test ", filter_end($c, ":end"));
    }

    public function testFilterNewLines() {
        $c = "test";
        $this->assertEquals("test", filter_newlines($c));
        $this->assertEquals("test", filter_newlines($c, true));

        $c = "test\n";
        $this->assertEquals("test ", filter_newlines($c));
        $this->assertEquals("test<br />\n", filter_newlines($c, true));

        $c = "test\r\n";
        $this->assertEquals("test ", filter_newlines($c));
        $this->assertEquals("test<br />\n", filter_newlines($c, true));

        $c = "test\r";
        $this->assertEquals("test ", filter_newlines($c));
        $this->assertEquals("test<br />\n", filter_newlines($c, true));

        $c = "test\n\n";
        $this->assertEquals("test ", filter_newlines($c));
        $this->assertEquals("test<br />\n", filter_newlines($c, true));

        $c = "test\r\n\r\n";
        $this->assertEquals("test ", filter_newlines($c));
        $this->assertEquals("test<br />\n", filter_newlines($c, true));

        $c = "test\r\n\r\ntest\n\ntest\rtest\r\ntest\ntest";
        $this->assertEquals("test test test test test test", filter_newlines($c));
        $this->assertEquals("test<br />\ntest<br />\ntest<br />\ntest<br />\ntest<br />\ntest", filter_newlines($c, true));
    }

    public function testGetNameFromEmail() {
        $this->assertEquals("", GetNameFromEmail(""));
        $this->assertEquals("Wayne", GetNameFromEmail('Wayne <wayne@devzing.com>'));
        $this->assertEquals("wayne", GetNameFromEmail('wayne@devzing.com'));
    }

    public function testGetPostType() {
        $subject = "test";
        $this->assertEquals("post", tag_PostType($subject));
        $this->assertEquals("test", $subject);

        $subject = "custom//test";
        $this->assertEquals("custom", tag_PostType($subject));
        $this->assertEquals("test", $subject);

        $subject = "//test";
        $this->assertEquals("post", tag_PostType($subject));
        $this->assertEquals("test", $subject);

        $subject = "//";
        $this->assertEquals("post", tag_PostType($subject));
        $this->assertEquals("", $subject);

        $subject = "Image//test";
        $this->assertEquals("image", tag_PostType($subject));
        $this->assertEquals("test", $subject);

        $subject = "Image // test";
        $this->assertEquals("image", tag_PostType($subject));
        $this->assertEquals("test", $subject);

        $subject = "video//test";
        $this->assertEquals("video", tag_PostType($subject));
        $this->assertEquals("test", $subject);
    }

    public function testGetPostExcerpt() {
        $c = "test";
        $this->assertEquals("", tag_Excerpt($c, false, false));

        $c = ":excerptstart test :excerptend test";
        $this->assertEquals("test ", tag_Excerpt($c, false, false));

        $c = ":excerptstart test";
        $this->assertEquals("", tag_Excerpt($c, false, false));

        $c = "test :excerptend test";
        $this->assertEquals("", tag_Excerpt($c, false, false));
    }

    public function testGetPostCategories() {
        global $wpdb;

        $s = "test";
        $c = tag_categories($s, "default");
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("test", $s);

        $s = ":test";
        $c = tag_categories($s, "default");
        $this->assertEquals("default", $c[0]);
        $this->assertEquals(":test", $s);

        $wpdb->t_get_var = "1";
        $s = "1: test";
        $c = tag_categories($s, "default");
        $this->assertEquals("1", $c[0]);
        $this->assertEquals("test", $s);

        $wpdb->t_get_var = null;
        $s = "not a category: test";
        $c = tag_categories($s, "default");
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("not a category: test", $s);

        $s = "[not a category] test";
        $c = tag_categories($s, "default");
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("[not a category] test", $s);

        $s = "-not a category- test";
        $c = tag_categories($s, "default");
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("-not a category- test", $s);

        $wpdb->t_get_var = "general";
        $s = "general: test";
        $c = tag_categories($s, "default");
        $this->assertEquals("general", $c[0]);
        $this->assertEquals("test", $s);

        $wpdb->t_get_var = "general";
        $s = "[general] test";
        $c = tag_categories($s, "default");
        $this->assertEquals("general", $c[0]);
        $this->assertEquals("test", $s);

        $wpdb->t_get_var = "general";
        $s = "-general- test";
        $c = tag_categories($s, "default");
        $this->assertEquals("general", $c[0]);
        $this->assertEquals("test", $s);

        $wpdb->t_get_var = "";
        $s = "specific: test";
        $c = tag_categories($s, "default");
        $this->assertEquals("default", $c[0]);
        $this->assertEquals("specific: test", $s);

        $wpdb->t_get_var = array("1", "1");
        $s = "[1] [1] test";
        $c = tag_categories($s, "default");
        $this->assertEquals(2, count($c));
        $this->assertEquals("1", $c[0]);
        $this->assertEquals("1", $c[1]);
        $this->assertEquals("test", $s);

        $wpdb->t_get_var = array(null, null, null, "general");
        $s = "[general] test: with colon";
        $c = tag_categories($s, "default");
        $this->assertEquals("general", $c[0]);
        $this->assertEquals("test: with colon", $s);
    }

    public function testHTML2HTML() {
        $this->assertEquals("", filter_CleanHtml(""));
        $this->assertEquals("test", filter_CleanHtml("test"));
        $this->assertEquals("<div>test</div>\n", filter_CleanHtml("<html lang='en'><body>test</body></html>"));
        $this->assertEquals("<div>test</div>\n", filter_CleanHtml("<html lang='en'><head><title>title</title></head><body>test</body></html>"));
        $this->assertEquals("<div>test</div>\n", filter_CleanHtml("<body>test</body>"));
        $this->assertEquals("<strong>test</strong>", filter_CleanHtml("<strong>test</strong>"));
    }

    public function testSafeFileName() {
        $this->assertEquals("testtest", SafeFileName('test\/:*?"<>|test'));
    }

    public function testremove_signature() {
        $this->assertEquals("", filter_RemoveSignature("", array()));
        $this->assertEquals("test", filter_RemoveSignature("test", array()));
        $this->assertEquals("\n", filter_RemoveSignature("", array("--", "- --")));
        $this->assertEquals("test\n", filter_RemoveSignature("test", array("--", "- --")));
        $this->assertEquals("line 1\nline 2\n", filter_RemoveSignature("line 1\nline 2\n--\nsig line 1\nsig line 2", array("--", "- --")));
        $this->assertEquals("line 1\nline 2\n", filter_RemoveSignature("line 1\nline 2\n- --\nsig line 1\nsig line 2", array("--", "- --")));
        $this->assertEquals("line 1\nline 2\n", filter_RemoveSignature("line 1\nline 2\n-- \nsig line 1\nsig line 2", array("--", "- --")));
        $this->assertEquals("line 1\nline 2\n", filter_RemoveSignature("line 1\nline 2\n --\nsig line 1\nsig line 2", array("--", "- --")));
        $this->assertEquals("line 1\nline 2\n", filter_RemoveSignature("line 1\nline 2\n--", array("--", "- --")));
    }

    public function testmore_reccurences() {
        $sched = array();
        $newsched = postie_more_reccurences($sched);
        $this->assertEquals(3, count($newsched));
    }

    public function testpostie_get_tags() {
        $c = "";
        $t = tag_Tags($c, "");
        $this->assertEquals(0, count($t));
        $this->assertEquals("", $c);

        $c = "test";
        $t = tag_Tags($c, "");
        $this->assertEquals(0, count($t));
        $this->assertEquals("test", $c);

        $c = "test";
        $t = tag_Tags($c, array("tag1"));
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test", $c);

        $c = "test tags:";
        $t = tag_Tags($c, "");
        $this->assertEquals(0, count($t));
        $this->assertEquals("test tags:", $c);

        $c = "test tags:\n";
        $t = tag_Tags($c, "");
        $this->assertEquals(0, count($t));
        $this->assertEquals("test tags:\n", $c);

        $c = "test tags: tag1";
        $t = tag_Tags($c, "");
        $this->assertEquals(1, count($t));
        $this->assertEquals("test ", $c);

        $c = "test\ntags: tag1";
        $t = tag_Tags($c, "");
        $this->assertEquals(1, count($t));
        $this->assertEquals("test\n", $c);

        $c = "test tags: tag1\n";
        $t = tag_Tags($c, "");
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test \n", $c);

        $c = "test tags:tag1";
        $t = tag_Tags($c, "");
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test ", $c);

        $c = "test tags:tag1";
        $t = tag_Tags($c, array("tagx"));
        $this->assertEquals(1, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("test ", $c);

        $c = "test tags:tag1,tag2";
        $t = tag_Tags($c, "");
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("tag2", $t[1]);
        $this->assertEquals("test ", $c);

        $c = "test tags: tag3,tag4\nmore stuff\n:end";
        $t = tag_Tags($c, "");
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag3", $t[0]);
        $this->assertEquals("tag4", $t[1]);
        $this->assertEquals("test \nmore stuff\n:end", $c);

        $c = "test tags:tag1,tag2\nmore stuff\n:end";
        $t = tag_Tags($c, "");
        $this->assertEquals(2, count($t));
        $this->assertEquals("tag1", $t[0]);
        $this->assertEquals("tag2", $t[1]);
        $this->assertEquals("test \nmore stuff\n:end", $c);
    }

    public function testclickableLink() {
        $this->assertEquals("", filter_linkify(""));
        $this->assertEquals("test", filter_linkify("test"));
        $this->assertEquals('<a href="http://www.example.com"  >http://www.example.com</a>', filter_linkify("http://www.example.com"));
        $this->assertEquals('<a href="http://www.example.com">www.example.com</a>', filter_linkify("www.example.com"));
        $this->assertEquals('<a href="http://www.example.com">www.example.com</a> <a href="http://www.example.com">www.example.com</a>', filter_linkify("www.example.com www.example.com"));
        $this->assertEquals('<a href="mailto:bob@example.com">bob@example.com</a>', filter_linkify("bob@example.com"));
        $this->assertEquals("<img src='http://www.example.com'/>", filter_linkify("<img src='http://www.example.com'/>"));
        $this->assertEquals("<html><head><title></title></head><body><img src='http://www.example.com'/></body></html>", filter_linkify("<html><head><title></title></head><body><img src='http://www.example.com'/></body></html>"));
        $this->assertEquals('<html><head><title></title></head><body><img src="http://www.example.com"/><a href="http://www.example.com">www.example.com</a></body></html>', filter_linkify('<html><head><title></title></head><body><img src="http://www.example.com"/>www.example.com</body></html>'));
    }

}

?>
