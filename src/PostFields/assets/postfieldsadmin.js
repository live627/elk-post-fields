function handleFields() {
    this.type = document.getElementById("field_type").value;
    this.isText = this.type == "text" || this.type == "textarea";
    this.regexMask = this.type == "text" || this.type == "textarea";
    this.regexFmt = this.type == "select";
    this.dimension = this.type == "textarea";
    this.size = this.type == "select";
    this.bbc = this.type == "text" || this.type == "textarea" || this.type == "select" || this.type == "radio" || this.type == "check";
    this.opts = this.type == "select" || this.type == "radio";
    this.def = this.type == "check";
}

function updateInputBoxes(b) {
    var hf = new handleFields();
    document.getElementById("max_length_dt").classList.toggle("slideInDown", hf.isText);
    document.getElementById("max_length_dd").classList.toggle("slideInDown", hf.isText);
    document.getElementById("dimension_dt").classList.toggle("slideInDown", hf.dimension);
    document.getElementById("dimension_dd").classList.toggle("slideInDown", hf.dimension);
    document.getElementById("size_dt").classList.toggle("slideInDown", hf.size);
    document.getElementById("size_dd").classList.toggle("slideInDown", hf.size);
    document.getElementById("bbc_dt").classList.toggle("slideInDown", hf.bbc);
    document.getElementById("bbc_dd").classList.toggle("slideInDown", hf.bbc);
    document.getElementById("options_dt").classList.toggle("slideInDown", hf.opts);
    document.getElementById("options_dd").classList.toggle("slideInDown", hf.opts);
    document.getElementById("default_dt").classList.toggle("slideInDown", hf.def);
    document.getElementById("default_dd").classList.toggle("slideInDown", hf.def);
    document.getElementById("mask_dt").classList.toggle("slideInDown", hf.regexMask);
    document.getElementById("mask_dd").classList.toggle("slideInDown", hf.regexMask);
}

function updateInputBoxes2(b) {
    regexMask = document.getElementById("field_mask").value == 'regex';
    document.getElementById("regex_dt").classList.toggle("slideInDown", regexMask);
    document.getElementById("regex_dd").classList.toggle("slideInDown", regexMask);
}

function addOption() {
    setOuterHTML(document.getElementById("addopt"), '<br><input type="radio" name="default_select" value="' + startOptID + '" id="' + startOptID + '"><input type="text" name="select_option[' + startOptID + ']" value="">');
    startOptID++;
}