console.log("Home index.js loaded...");

window.addEventListener("load", () => {
  const btn = document.getElementById("btn");

  btn.addEventListener("click", () => {
    const ext = document.getElementById("ext").value || "json";
    console.log("Fetching users with extension: ", ext);
    fetch("/users?ext=" + ext).then(async (res) => {
      if (!res.ok) {
        console.error(res);
      } else if (res.status === 204) {
        throw new Error("No content found");
      }
      if (res.headers.get("content-type") === "application/json") {
        console.log(await res.json());
      } else {
        blob = await res.blob();
        const contentDispo = res.headers.get("content-disposition");
        if (!contentDispo) {
          return;
        }
        const fileName = contentDispo.split("filename=")[1];
        console.log(name);
        // create object URL
        const url = URL.createObjectURL(blob);
        // create a link element
        const a = document.createElement("a");
        // set the href attribute
        a.href = url;
        // set the download attribute
        a.download = fileName;
        // click the link
        a.click();
        // remove the link
        a.remove();
      }
    });
  });
});
