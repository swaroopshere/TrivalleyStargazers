const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');

function formHarness() {
    const listeners = {}, windowListeners = {}, timers = [];
    const elements = {
        submit1: {id:'submit1', value:'New member', disabled:false},
        submit2: {id:'submit2', value:'Returning member', disabled:false},
        preset: {value:''}, new: {value:'unset'}, applicationStatus: {textContent:''}
    };
    const form = {addEventListener:(name, fn)=>listeners[name]=fn,
        setAttribute:(key,value)=>form[key]=value, removeAttribute:key=>delete form[key]};
    vm.runInNewContext(fs.readFileSync(path.join(root,'membership.js'),'utf8'), {
        document: {readyState:'complete', forms:{application:form}, getElementById:id=>elements[id]},
        window: {addEventListener:(name,fn)=>windowListeners[name]=fn},
        setTimeout:fn=>{timers.push(fn);return timers.length;}, clearTimeout:()=>{}
    });
    const submit = id => {
        const event = {submitter:elements[id], prevented:false, preventDefault(){this.prevented=true;}};
        listeners.submit(event); return event;
    };
    return {elements, form, submit, timers, windowListeners};
}

test('new application submits once, reports progress, and preserves hidden status',()=>{
    const h = formHarness();
    assert.equal(h.elements.preset.value,'Preset');
    assert.equal(h.submit('submit1').prevented,false);
    assert.equal(h.elements.new.value,'Yes');
    assert.equal(h.elements.submit1.disabled,true);
    assert.equal(h.elements.submit2.disabled,true);
    assert.match(h.elements.applicationStatus.textContent,/Submitting/);
    assert.equal(h.submit('submit1').prevented,true);
    h.timers[0]();
    assert.match(h.elements.applicationStatus.textContent,/do not submit again/);
});
test('renewal is preserved and Back navigation restores both buttons',()=>{
    const h = formHarness();
    h.submit('submit2');
    assert.equal(h.elements.new.value,'No');
    h.windowListeners.pageshow();
    assert.equal(h.elements.submit1.disabled,false);
    assert.equal(h.elements.submit2.value,'Returning member');
    assert.equal(h.elements.applicationStatus.textContent,'');
    assert.equal(h.submit('submit1').prevented,false);
});
test('Enter submission defaults to a new application and older browser renewal still works',()=>{
    let h = formHarness(); h.submit(); assert.equal(h.elements.new.value,'Yes');
    h = formHarness(); h.elements.new.value='No'; h.submit(); assert.equal(h.elements.new.value,'No');
});
test('form remains a native POST and payment confirmation keeps a PDF fallback',()=>{
    const html = fs.readFileSync(path.join(root,'membership.shtml'),'utf8');
    assert.match(html, /action="cgi-bin\/apply.pl" method="post"/);
    const pay = fs.readFileSync(path.join(root,'pay.shtml'),'utf8');
    assert.match(pay,/application has been received/);
    assert.doesNotMatch(pay,/href="TVSapplication.pdf"/);
});

function paymentHarness(options={}) {
    const elements = {
        membershipType: {value: options.membership || '30', selectedIndex:0,
            options:[{text:options.membership==='10'?'Student $10':'Regular $30'}]},
        H2OKey:{checked:!!options.key,value:'20'}, H2OAccess:{checked:!!options.access,value:'10'},
        donation:{value:options.donation || '0'}, other:{value:options.other || '0'},
        explanation:{value:options.explanation || ''}, total:{value:''}
    };
    const alerts=[], navigations=[];
    const location = {search:options.sandbox?'?sandbox':'?applicant=true',assign:url=>navigations.push(url)};
    const document = {getElementById:id=>elements[id],addEventListener:()=>{},body:{onbeforeunload:()=>{}}};
    const context = {document,window:{location},location,alert:message=>alerts.push(message),setTimeout,clearTimeout};
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(path.join(root,'tvs.js'),'utf8'),context);
    return {context,elements,alerts,navigations};
}
test('regular membership plus access/key/donation/other preserves PayPal cart and total',()=>{
    const h=paymentHarness({key:true,access:true,donation:'$5.50',other:'12',explanation:'Handbook'});
    h.context.updateTotal();
    assert.equal(h.elements.total.value,'$77.5');
    h.context.callPayPal();
    assert.equal(h.navigations.length,1);
    const url=new URL(h.navigations[0]);
    assert.equal(url.origin,'https://www.paypal.com');
    assert.equal(url.searchParams.get('business'),'treasurer@trivalleystargazers.org');
    assert.equal(url.searchParams.get('cmd'),'_cart');
    assert.equal(url.searchParams.get('currency_code'),'USD');
    const amounts=Array.from({length:5},(_,i)=>Number(url.searchParams.get('amount_'+(i+1))));
    assert.equal(amounts.reduce((a,b)=>a+b,0),77.5);
    assert.equal(url.searchParams.get('os0_5'),'Handbook');
});
test('student, returning member, and sandbox payment destinations are preserved',()=>{
    const h=paymentHarness({membership:'10',sandbox:true});
    h.context.callPayPal();
    const url=new URL(h.navigations[0]);
    assert.equal(url.origin,'https://www.sandbox.paypal.com');
    assert.equal(url.searchParams.get('business'),'treasurer-facilitator@trivalleystargazers.org');
    assert.equal(url.searchParams.get('amount_1'),'10');
});
test('invalid donation and missing other explanation do not navigate',()=>{
    for(const options of [{donation:'invalid'},{other:'10'}]) {
        const h=paymentHarness(options); h.context.callPayPal();
        assert.equal(h.navigations.length,0);
        assert.ok(h.alerts.length>0);
    }
});
