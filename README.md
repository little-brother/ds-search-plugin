Simple search plugin for [Synology Download Station](https://www.synology.com/en-global/dsm/packages/DownloadStation) with auth/cookies. 

There are two problems:
1. The tracker requires auth cookies to download a torrent file;
2. The tracker may not be directly accessible.

The solution is the search plugin have to rewrite download links to a mediator that supports requests with cookies and can use a proxy to access to the tracker. 
The repository contains a [DLM-plugin](https://github.com/little-brother/ds-search-plugin/tree/master/bt-rutracker) for [rutracker.org](https://rutracker.org) as an example and the [mediator app](https://github.com/little-brother/ds-search-plugin/tree/master/get-proxy) as a docker container. Check [Wiki](https://github.com/little-brother/ds-search-plugin/wiki) for details.
