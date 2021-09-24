import DOM from "./DOM.js";
import PubSub from "./PubSub.js";
import Procrastinator from "./Procrastinator.js";
//http://localhost:8983/solr/mycore/select?q=Cadbury%20AND%20Azure

//async function solrSearch(URL)

function SolrSearcher(spec) {
  let self = PubSub();

  let solrCoreBaseURL = "http://solr:8983/solr/mycore/";

  let waiter = Procrastinator();

  self.onSearchInput = function(e) {
    let keywords = e.target.value.split(/\s+/).filter((o) => o && o.length);
    waiter(
      "new-search",
      async function() {
        //return console.log("CALLED << >> ", keywords, Math.random());
        await self.doSearch(keywords);
      },
      350
    );
  };

  let myWrap = DOM.div()
    .addClass("solr-search flex-row")
    .append(
      DOM.input()
        .attr("type", "text")
        .attr("placeholder", "keywords")
        .on("change keyup blur", self.onSearchInput)
      //.on("change", self.onSearchInput)
    );

  self.doSearch = async function(keywords) {
    let joined = keywords.join(" AND ");
    //console.log("joined", joined);
    let queryString = encodeURI(joined);
    let fullURL = `/solr-search/?q=${queryString}`;
    //console.log("fullURL", fullURL);

    let resp = await fetch(fullURL);
    let json = await resp.json();
    //console.log("resp json", json);

    if (json && json.response && json.response.docs) {
      return self.emit("search-results", json.response.docs, keywords);
    }
    return self.emit("search-results", [], keywords);
  };

  self.ui = function() {
    return myWrap;
  };
  self.renderOn = function(wrap) {
    wrap.append(self.ui());
  };
  return self;
}

export default SolrSearcher;
