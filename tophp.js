/* $Id: tophp.js,v 1.6 2004/06/24 15:21:11 harryf Exp $ */
Number.prototype.toPHP=function() {
    if (Math.round(this) == this) {
        return 'i:'+this+';';
    } else {
        return 'd:'+this+';';
    };
};
String.prototype.toPHP=function() {
    var s = this;
    return 's:'+s.length+':"'+s+'";';
};
Boolean.prototype.toPHP=function() {
    if (this == true) {
        return 'b:1;';
    } else {
        return 'b:0;';
    };
};
Function.prototype.toPHP=function() {
    return 'N;';
};
Array.prototype.toPHP=function() {
    var a=this;
    var indexed = new Array();
    var count = a.length;
    var s = '';
    for (var i=0; i<a.length; i++) {
        indexed[i] = true;
        s += 'i:'+i+';'+a[i].toPHP();
    };
    for ( var prop in a ) {
        if ( prop == 'var_dump' || prop == 'toPHP' ) {
            continue;
        };
        if ( indexed[prop] ) {
            continue;
        };
        s += prop.toPHP()+a[prop].toPHP();
        count++;        
    };    
    s = 'a:'+count+':{'+s;
    s += '}';
    return s;
};
Object.prototype.toPHP=function() {
    var o=this;
    var cname = 'ScriptServer_Object';
    if (o==null) return 'N;';  
    var s='';
    var count=0;
    for (var prop in o) {
        if ( prop == 'var_dump' || prop == 'toPHP' ) {
            continue;
        };
        count++;
        s += 's:'+prop.length+':"'+prop+'";';
        if (o[prop]==null) {
            s +='N;';
        } else {
            s += o[prop].toPHP();
        };
    };
    s = 'O:'+cname.length+':"'+cname.toLowerCase()+'":'+count+':{'+s+'}';   
    return s;
};
Error.prototype.toPHP=function() {
    var e=this;
    var cname = 'ScriptServer_Error';
    var s='';
    s += 's:4:"name";';
    s += e.name.toPHP();
    s += 's:7:"message";';
    s += e.message.toPHP();    
    s = 'O:'+cname.length+':"'+cname.toLowerCase()+'":2:{'+s+'}';
    return s;
};