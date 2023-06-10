//when the worker gets a message, send the data to the usage function
addEventListener("message", (message) => {
    getDiskUsage(message.data.jobState,message.data.url);
});


async function getDiskUsage(state,url) {

    //create the api route url
    var addr = url;
    addr += "/wordpress/wp-json/testplugin/api/disk?start=" + state;
    
    //make the request and return the response as an array
    let ret = await fetch(addr).then(resp => resp.json()).then(data=> {
        return JSON.parse(data);
    });
    
    
    //send the data back to the main thread.
    postMessage(ret);

}