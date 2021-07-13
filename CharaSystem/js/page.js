let page = 0;
let voices;
let select;
let param = {};
let audio;

function changehash(key, value) {
    param[key] = value;
    let h = "";
    for (let i in param) {
        h += '&' + i + '=' + param[i];
    }
    return location.hash = /* "#" + */ h.slice(1);
}

function triggerF() {
    follow = follow ? 0 : 1;
    load();
    changehash("f", follow);
}
function resize() {
    let w = select.width.val();
    let h = select.height.val();
    init(parseInt(w), parseInt(h));
    app.view.style.width = (parseFloat(select.zoom.val()) * parseFloat(w) / 100 + "px");
    load();
    changehash("w", w);
    changehash("h", h);
}
const downloadBlob = function(index) {
    return function(blob) {
        const a = document.createElement("a");
        a.download = index;
        a.href = window.URL.createObjectURL(blob);
        a.click();
        window.URL.revokeObjectURL(a.href);
    }
}
function save(index) { app.view.toBlob(downloadBlob(index)); }
function capture() { save(page++); }

function get(url, callback) {
    return $.getJSON({
        url: url,
        success: function(data) {callback(data)},
        error: function(xhr) {alert(xhr.responseJSON)}
    })
}

function hash() {
    if (location.hash && location.hash[0] == '#') {
        let arr = location.hash.slice(1).split('&');
        for (let i in arr) {
            let kv = arr[i].split('=');
            if (kv[1]) switch (kv[0]) {
                case "id":
                    param[kv[0]] = kv[1];
                    break;
                case "exp":
                case "mo":
                case "vo":
                case "z":
                case "w":
                case "h":
                    param[kv[0]] = parseInt(kv[1]);
                    break;
                case "f":
                    param[kv[0]] = parseInt(kv[1]) || 1;
                    break;
            }
        }
    }
    return param;
}

function load(id) {
    if (!id) id = select.custom.val();
    let lastChild = null;
    while (lastChild = stage.children.shift()) { lastChild.destroy(); }
    show("/resource/image_native/live2d/" + id + "/", "model.json", function(model) {
        select.exp.empty();
        for (let c in model.expressions) {
            let i = model.expressions[c];
            select.exp.append($("<option></option>").text(c).val(i.name));
        }
        select.exp.val(param.exp ? model.expressions[param.exp].name : "").change();

        select.motion.empty();
        for (let c in model.motions.motion) {
            let i = model.motions.motion[c];
            select.motion.append($("<option></option>").text(i.name).val(c));
        }
        select.motion.val(param.mo || "").change();
    });
}

$(document).ready(function() {
    select = {
        width: $("#width"),
        height: $("#height"),
        zoom: $("#zoom"),
        char: $("#char"),
        custom: $("#custom"),
        exp: $("#function > #g1 > select.exp"),
        motion: $("#function > #g1 > select.motion"),
    }
    hash();
    select.width.val(param.w || 900);
    select.height.val(param.h || 1600);
    init(param.w || 900, param.h || 1600);

    get("/resource/image_native/live2d/list.json", function (list) {
        select.char.empty();
        for (let c in list) {
            char = list[c];
            select.char.append($("<option></option>").text(c + "-" + char.name).val(c));
        }
        select.char.change(function() {
            if (!this.value || !(this.value in list)) return;
            select.custom.empty();
            for (let c in list[this.value]["models"]) {
                custom = list[this.value]["models"][c];
                select.custom.append($("<option></option>").text(c + "-" + custom).val(c));
            }
            if (param.id && param.id.slice(0, 4) == this.value) {
                select.custom.val(param.id).change();
            } else {
                select.custom.val("");
                changehash("id", this.value);
            }
        });

        select.zoom.change(function() {
            if (!this.value) this.value = 50;
            $("#Lzoom").text("Zoom: " + this.value + "%");
            app.view.style.width = (parseFloat(this.value) * parseFloat($("#width").val()) / 100 + "px");
            changehash("z", this.value);
        });
        if (param.z) select.zoom.val(param.z).change();

        select.custom.change(function() {
            if (!this.value) return;
            if (param.id != this.value) {
                delete param.exp;
                delete param.mo;
                delete param.vo;
                changehash("id", this.value);
            }
            load(this.value);
        });
        if (param.id) {
            select.char.val(param.id.slice(0,4)).change();
            select.custom.val(param.id).change();
            load();
        } else {
            select.char.val("");
        }

        select.exp.change(function() {
            if (!this.value) return;
            stage.children[0].model.setExpression(this.value);
            changehash("exp", this.selectedOptions[0].innerText);
        });

        select.motion.change(function() {
            if (!this.value) return;
            stage.children[0].model.startMotion("motion", this.value);
            changehash("mo", this.value);
        });
    });
});