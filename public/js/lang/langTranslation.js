
/**
 * it's use for get translation in js file
 * @Author Mr amit mehra and Mr Harbans singh
 */


const _language = { 
    getLanString(str) {
        if(LangObjectJS[str] == undefined){
            return str;
        }
        return LangObjectJS[str];
    }
}