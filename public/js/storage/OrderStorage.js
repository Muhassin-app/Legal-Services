/**
 * Store all portal localstorage
 * @Author Mr amit mehra and Mr Harbans singh
 */

const OrderStorage = {
    setStorageAll(type_id,slot_id,type) {
       
    },
    setStorageSingle(item,value) {
        localStorage.setItem(item,value);
    },
    removeStorageAll(){
        localStorage.clear();
    },
    removeStorageSingle(item){
        localStorage.removeItem(item);
    },
    getStorage(item){
        var returnValue = localStorage.getItem(String(item));
        return (returnValue) ? returnValue :'';
    }
}

/**
 * Store all portal localstorage
 * @Author Mr Harbans singh
 */

const OrderSessionStorage = {
  
    setStorageSingle(item,value) {
        sessionStorage.setItem(item,value);
    },
    removeStorageAll(){
        sessionStorage.clear();
    },
    removeStorageSingle(item){
        sessionStorage.removeItem(item);
    },
    getStorage(item){
        var returnValue = sessionStorage.getItem(String(item));
        return (returnValue) ? returnValue :'';
    }
}