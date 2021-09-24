<?php
add_filter('wp_title', function ($original) {
  return 'HOME';
  ///return $original;
});

get_header(); ?>


<div class="three-across full-width flex-row space-around">
  <div class="flex-column one-third-width">
    <div class="explain">
      <span class="big-number">1</span>

      Dragging files here will:
      <ul>
        <li>generate a SHA256 <strong>content-based address</strong> for each file</li>
        <li>Locally, Apache Solr's included Apache Tika module will then extract metadata from your files and index the <strong>metadata</strong> (including the content-based address) into your local Apache Solr instance</li>
        <li>upload each file's <strong>content</strong> to your remote AWS S3 bucket with a key based on the content-based address</li>
      </ul>
      <div class="drag-wrap flex-row space-around">
      </div>
      <div class="errors"></div>

    </div>

  </div>


  <div class="flex-column one-third-width">
    <div class="explain">
      <span class="big-number">2</span>
      Search your local Apache Solr instance for documents with metadata matching your keywords
      <div class="search flex-row space-around pad8">
      </div>

    </div>

  </div>
  <div class="flex-column one-third-width">
    <div class="explain">
      <span class="big-number">3</span>
      Search results display with a <i class="fa fa-download"></i> download link which will pull file content from your AWS S3 Bucket</li>
      </ul>
      <div class="results">
      </div>
    </div>
  </div>

</div>
<?php
add_action('wp_footer', function () {
?>
  <script type="module">
    import DOM from "./js/DOM.js";
    import Procrastinator from "./js/Procrastinator.js";
    import SolrSearcher from "./js/SolrSearcher.js";
    import DragAndDropFile from "./js/DragAndDropFile.js";
    //import PubSub from "./js/PubSub.js";


    function extractField(val) {
      if (!val) {
        return '';
      }
      if (Array.isArray(val)) {
        return val[0];
      }
      return val;
    }

    function describeDoc(doc) {
      let props = ['title', 'filename', 'content_type', 'mime_type', 'creator', 'author', 'dc_creator', 'version', 'stream_size', 'samplerate']
      let content = props.map(label => {
          if (!doc[label]) {
            return false;
          }
          return DOM.div()
            .addClass('label-span-pair')
            .append([
              DOM.label(label),
              DOM.span(extractField(doc[label]))
            ])

        })
        .filter(o => o) //get rid of empties

      return DOM.div()
        .addClass('describe-doc two-thirds-width')
        .append(content);
      return content;
    }

    let searchWrap = jQuery('.search');
    let resultsWrap = jQuery('.results');
    let searcher = SolrSearcher();
    searchWrap.append(searcher.ui())
    searcher.on('search-results', function(docs, keywords) {
      resultsWrap
        .empty()
        .append(docs.map(doc => {

          return DOM.div()
            .addClass('flex-row search-result space-between')
            .append([
              ///DOM.span(displayString),
              DOM.a()
              .attr('target', '_blank') //open in new tab
              .attr('href', `${window.location.href}/inline/${doc.sha256}`)
              .append(
                DOM.i()
                .addClass('fa fa-download')
              ),
              describeDoc(doc),

            ])
        }))
    })

    function handleFiles(aFileList) {
      let files = [...aFileList];
      //initializeProgress(files.length)
      files.forEach(uploadFile)
    }

    function uploadFile(file, i) {
      var xhr = new XMLHttpRequest()
      var formData = new FormData()
      xhr.open('POST', '/upload', true)
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
      // Update progress (can be used to show progress indicator)
      xhr.upload.addEventListener("progress", function(e) {
        //updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
      })
      xhr.addEventListener('readystatechange', function(e) {
        if (xhr.readyState == 4 && xhr.status == 200) {
          updateProgress(i, 100) // <- Add this
        } else if (xhr.readyState == 4 && xhr.status != 200) {
          // Error. Inform the user
          console.log(xhr.status, xhr);
          document.querySelector('.errors').innerHTML += xhr.response;
        }
      })
      formData.append('file', file)
      xhr.send(formData)
    }
    let dragWrap = jQuery('.drag-wrap');
    let dad = DragAndDropFile({
      accept: 'pdf,epub,mp3,mp4,m4v,png,jpg,svg,gif,txt,doc,docx,xls,xlsx,ppt,pptx',
      handleFiles: handleFiles
    });
    dragWrap.append(dad.ui())
  </script>


<?php
});


get_footer();
