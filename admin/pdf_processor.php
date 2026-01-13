<?php
/**
 * PDF Processing Functions
 * Extracts first page of PDF as image for newsletter cover
 */
require_once __DIR__ . '/config.php';

/**
 * Generate newsletter cover image from PDF first page
 * 
 * @param string $pdfPath Path to PDF file
 * @return bool Success status
 */
function generateNewsletterCover($pdfPath) {
    if (!file_exists($pdfPath)) {
        return false;
    }
    
    // Try multiple methods to extract first page
    
    // Method 1: ImageMagick (most common)
    if (function_exists('exec') && is_executable(IMAGEMAGICK_PATH)) {
        $output = NEWSCOVER_PATH;
        $command = escapeshellarg(IMAGEMAGICK_PATH) . ' ' . 
                   escapeshellarg($pdfPath . '[0]') . ' ' . 
                   '-quality 90 -resize 800x ' . 
                   escapeshellarg($output) . ' 2>&1';
        
        exec($command, $output_array, $return_code);
        if ($return_code === 0 && file_exists($output)) {
            return true;
        }
    }
    
    // Method 2: Ghostscript
    if (function_exists('exec') && is_executable(GHOSTSCRIPT_PATH)) {
        $output = NEWSCOVER_PATH;
        $command = escapeshellarg(GHOSTSCRIPT_PATH) . 
                   ' -dNOPAUSE -dBATCH -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 ' .
                   '-sOutputFile=' . escapeshellarg($output) . ' ' .
                   escapeshellarg($pdfPath) . ' 2>&1';
        
        exec($command, $output_array, $return_code);
        if ($return_code === 0 && file_exists($output)) {
            return true;
        }
    }
    
    // Method 3: Try ImageMagick with 'convert' command (common alias)
    if (function_exists('exec')) {
        $commands = ['convert', '/usr/local/bin/convert', '/opt/local/bin/convert'];
        foreach ($commands as $cmd) {
            $command = escapeshellarg($cmd) . ' ' . 
                       escapeshellarg($pdfPath . '[0]') . ' ' . 
                       '-quality 90 -resize 800x ' . 
                       escapeshellarg(NEWSCOVER_PATH) . ' 2>&1';
            
            exec($command, $output_array, $return_code);
            if ($return_code === 0 && file_exists(NEWSCOVER_PATH)) {
                return true;
            }
        }
    }
    
    // Method 4: Try Ghostscript with 'gs' command
    if (function_exists('exec')) {
        $commands = ['gs', '/usr/local/bin/gs', '/opt/local/bin/gs'];
        foreach ($commands as $cmd) {
            $command = escapeshellarg($cmd) . 
                       ' -dNOPAUSE -dBATCH -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 ' .
                       '-sOutputFile=' . escapeshellarg(NEWSCOVER_PATH) . ' ' .
                       escapeshellarg($pdfPath) . ' 2>&1';
            
            exec($command, $output_array, $return_code);
            if ($return_code === 0 && file_exists(NEWSCOVER_PATH)) {
                return true;
            }
        }
    }
    
    // Method 5: PHP Imagick extension (if available)
    if (extension_loaded('imagick')) {
        try {
            $imagick = new Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]');
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);
            $imagick->resizeImage(800, 0, Imagick::FILTER_LANCZOS, 1);
            $imagick->writeImage(NEWSCOVER_PATH);
            $imagick->clear();
            $imagick->destroy();
            
            if (file_exists(NEWSCOVER_PATH)) {
                return true;
            }
        } catch (Exception $e) {
            error_log("Imagick error: " . $e->getMessage());
        }
    }
    
    // Method 6: Try pdftoppm (poppler-utils)
    if (function_exists('exec')) {
        $commands = ['pdftoppm', '/usr/local/bin/pdftoppm', '/opt/local/bin/pdftoppm'];
        foreach ($commands as $cmd) {
            $tempOutput = dirname(NEWSCOVER_PATH) . '/temp_cover.jpg';
            $command = escapeshellarg($cmd) . 
                       ' -f 1 -l 1 -jpeg -r 150 ' .
                       escapeshellarg($pdfPath) . ' ' .
                       escapeshellarg(dirname($tempOutput) . '/temp_cover') . ' 2>&1';
            
            exec($command, $output_array, $return_code);
            $generatedFile = dirname($tempOutput) . '/temp_cover-1.jpg';
            if ($return_code === 0 && file_exists($generatedFile)) {
                if (rename($generatedFile, NEWSCOVER_PATH)) {
                    return true;
                }
            }
        }
    }
    
    return false;
}

/**
 * Regenerate newsletter.shtml dynamically
 */
function regenerateNewsletterPages($pdo) {
    // Get latest newsletter
    $stmt = $pdo->query("SELECT * FROM newsletters ORDER BY year DESC, month DESC LIMIT 1");
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$latest) {
        return false; // No newsletters yet
    }
    
    // Generate newsletter.shtml
    $year = $latest['year'];
    $month = str_pad($latest['month'], 2, '0', STR_PAD_LEFT);
    $shortYear = substr($year, -2);
    $newsletterPath = "newsletters/{$year}/tvsnews{$month}{$shortYear}.pdf";
    
    $content = generateNewsletterShtml($newsletterPath, $year, $latest['month']);
    file_put_contents(__DIR__ . '/../newsletter.shtml', $content);
    
    // Generate newsletterlinks.shtml
    $stmt = $pdo->query("SELECT * FROM newsletters ORDER BY year DESC, month DESC");
    $allNewsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $linksContent = generateNewsletterLinksShtml($allNewsletters);
    file_put_contents(__DIR__ . '/../newsletterlinks.shtml', $linksContent);
    
    return true;
}

/**
 * Generate newsletter.shtml content
 */
function generateNewsletterShtml($latestNewsletterPath, $year, $month) {
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    $lastUpdateDate = date('F j, Y');
    
    return <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <meta name="author" content="Hilary Jones  Ron Kane Swaroop Shere">
  <meta name="copyright" content="Tri-Valley Stargazers, 2022">
  <meta name="description" content="The Tri-Valley Stargazers newsletters">
  <meta name="keywords" content="Tri-Valley, Stargazers, astronomy">
  <title>Tri-Valley Stargazers newsletters</title>

  <link href="tvs.css" rel="stylesheet" type="text/css">

  <script src="tvs.js" type="text/javascript"></script>

  <script type="text/javascript">
   <!--
    lastUpdateDate = "{$lastUpdateDate}";
   //-->
  </script>
  <!-- The id for the button that needs to be underlined. -->
  <style type='text/css'>#appleNav li a#m_newsletter { text-decoration: underline;}</style>

 </head>

 <body>
<!--#include virtual="free_find.shtml" -->
<!--#include virtual="leader.shtml" -->
    <h1 class="title">Newsletter</h1>
    <br>

    <div class="content" style="font-size:medium; text-align:center; width:100%; margin-bottom:30px;">

     <b>
     <a title="This month's Prime Focus" href="{$latestNewsletterPath}#zoom=100&amp;pagemode=none" target="_top">
      View the latest newsletter:<br><br>
       <img src="images/newscover.jpg" title="Click here to view the latest newsletter" alt="Newsletter cover"
	style="border:ridge; border-color:black; border-width:1px;">
      </a>

     </b>
     <br><br><br><br>
     <b>View older newsletters:</b><br>
     <table align="center">
      <tr>
       <td>
        <form style="display:inline;" action=""><select id="theMonth">
         <option value="01">January</option>
         <option value="02">Februrary</option>
         <option value="03">March</option>
         <option value="04">April</option>
         <option value="05">May</option>
         <option value="06">June</option>
         <option value="07">July</option>
         <option value="08">August</option>
         <option value="09">September</option>
         <option value="10">October</option>
         <option value="11">November</option>
         <option value="12">December</option>
        </select></form>
       </td>
       <td>
        <form style="display:inline;" action=""><select id="theYear">
         <!-- table's option tags are created by script defineNewsletterYears -->
	 <option style="display:none;" value="00">Gets replaced</option>   <!-- Provided only to ensure valid HTML -->
        </select></form>
       </td>
       <td>
        <button type="button" onclick="goFetch();">Display this newsletter</button>
       </td>
      </tr>
     </table>
     <script type="text/javascript">
      <!--
       defineNewsletterYears();
      //-->
     </script>
     <br><br>

     To contribute to the newsletter, please contact the editor
     <script type="text/javascript">
      <!--
       contact("newsletter", "trivalleystargazers.org", "Scott Schneider");
       //-->
     </script>.
     

     <div class="newsletterlinks">
      <!-- 
        Invisible list of all newsletter links.  This is only used by FreeFind and could be deleted
	if the service went away.  Be sure to edit this file every time a new newsletter is added.
      -->
      <a href="newsletterlinks.shtml">Newsletter list</a>
     </div>

    </div>		<!-- end div class=content -->

<!--#include virtual="trailer.shtml" -->

</body>
</html>
HTML;
}

/**
 * Generate newsletterlinks.shtml content
 */
function generateNewsletterLinksShtml($newsletters) {
    $content = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <!-- This web page is provided to help FreeFind index all of the newsletters -->
 <head>
  <title>Newsletter links</title>
 </head>
 <body>

  The most recent news letters, which all use PDF files:<br>
HTML;

    // Group newsletters by year
    $grouped = [];
    foreach ($newsletters as $newsletter) {
        $year = $newsletter['year'];
        if (!isset($grouped[$year])) {
            $grouped[$year] = [];
        }
        $grouped[$year][] = $newsletter;
    }
    
    // Sort years descending
    krsort($grouped);
    
    // Generate links for PDF newsletters (2001-09 and later)
    foreach ($grouped as $year => $yearNewsletters) {
        // Sort by month descending
        usort($yearNewsletters, function($a, $b) {
            return $b['month'] - $a['month'];
        });
        
        foreach ($yearNewsletters as $newsletter) {
            // Only include PDF newsletters (from 2001-09 onwards)
            if ($year > 2001 || ($year == 2001 && $newsletter['month'] >= 9)) {
                $month = str_pad($newsletter['month'], 2, '0', STR_PAD_LEFT);
                $shortYear = substr($year, -2);
                $link = "newsletters/{$year}/{$newsletter['filename']}";
                $label = "{$month}'{$shortYear}";
                $content .= "  <a href=\"{$link}\">{$label}</a><br>\n";
            }
        }
    }
    
    // Add HTML newsletters section (pre-2001-09)
    $content .= "\n  <br>\n  Older newsletters, which all use HTML files:<br>\n";
    
    // Note: HTML newsletters are not in the database, so we'll keep the existing ones
    // You may want to add them to the database or maintain a separate list
    $htmlNewsletters = [
        ['year' => 2001, 'month' => 8, 'path' => 'newsletters/2001/0801/index.html'],
        ['year' => 2001, 'month' => 7, 'path' => 'newsletters/2001/0701/index.html'],
        ['year' => 2001, 'month' => 6, 'path' => 'newsletters/2001/0601/index.html'],
        ['year' => 2001, 'month' => 5, 'path' => 'newsletters/2001/0501/index.html'],
        ['year' => 2001, 'month' => 4, 'path' => 'newsletters/2001/0401/index.html'],
        ['year' => 2001, 'month' => 3, 'path' => 'newsletters/2001/0301/index.html'],
        ['year' => 2001, 'month' => 2, 'path' => 'newsletters/2001/0201/index.html'],
        ['year' => 2001, 'month' => 1, 'path' => 'newsletters/2001/0101/index.html'],
    ];
    
    // Add years 2000, 1999, 1998, 1997, 1996
    for ($y = 2000; $y >= 1996; $y--) {
        for ($m = 12; $m >= 1; $m--) {
            $monthPadded = str_pad($m, 2, '0', STR_PAD_LEFT);
            $shortYear = substr($y, -2);
            $path = "newsletters/{$y}/{$monthPadded}{$shortYear}/index.html";
            $label = "{$monthPadded}'{$shortYear}";
            $content .= "  <a href=\"{$path}\">{$label}</a><br>\n";
        }
    }
    
    $content .= <<<HTML

 </body>
</html>
HTML;

    return $content;
}
?>

