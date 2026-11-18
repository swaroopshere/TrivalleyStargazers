const test=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs'); const os=require('node:os'); const path=require('node:path'); const vm=require('node:vm');
const {normalizeCalendar,buildCalendar}=require('./public-calendar');
const event=(extra={})=>({id:'event-1',title:'School Star Party',start:'2026-12-08T18:00:00-08:00',end:'2026-12-08T21:00:00-08:00',description:'<p>Bring telescopes &amp; binoculars.</p>',location:'School',view_url:'https://groups.io/g/trivalleystargazers/viewevent?eventid=1',...extra});
test('local dates, actual start/end times, HTML text and stable occurrence identity',()=>{
 const rows=normalizeCalendar([event(),event(),event({start:'2027-01-08T18:00:00-08:00',end:'2027-01-08T21:00:00-08:00'})],'2026-11-18','2028-01-01');
 assert.equal(rows.length,2);assert.equal(rows[0].date,'2026-12-08');assert.equal(rows[0].time,'6:00 PM–9:00 PM');
 assert.equal(rows[0].description,'Bring telescopes & binoculars.');assert.notEqual(rows[0].id,rows[1].id);
});
test('cancelled, private, board and member-only entries stay out of the public export',()=>{
 const rows=normalizeCalendar([event({is_canceled:true}),event({is_private:true}),event({title:'TVS Board Meeting'}),event({description:'Members only'}),event({title:'Members-only trip'}),event({title:'H2O open house',description:'For members and the public'})],'2026-11-18','2028-01-01');
 assert.equal(rows.length,1);assert.equal(rows[0].category,'h2o');
});
test('meeting access tokens are omitted and curated November location takes precedence',()=>{
 const rows=normalizeCalendar([event({title:'TVS Club Meeting',description:'Zoom: https://zoom.us/j/123?pwd=private'})],'2026-11-18','2028-01-01',[{meeting_type:'public',meeting_date:'2026-12-08',location:'Church',location_address:'Livermore',description:'Doors open at 7 PM.',presentation_topic:'Talk',presenter_name:'Speaker'}]);
 assert.equal(rows[0].location,'Church, Livermore');assert(!JSON.stringify(rows).includes('pwd='));assert(rows[0].description.includes('Doors open'));
});
test('Pacific day and daylight-saving conversion use the actual event offset',()=>{
 const rows=normalizeCalendar([event({start:'2027-03-15T02:30:00Z',end:'2027-03-15T03:30:00Z'})],'2026-11-18','2028-01-01');
 assert.equal(rows[0].date,'2027-03-14');assert.equal(rows[0].time,'7:30 PM–8:30 PM');
});
test('bad feed shape, invalid times and foreign detail links fail rather than publishing',()=>{
 assert.throws(()=>normalizeCalendar({},'2026-11-18','2028-01-01'));
 assert.throws(()=>normalizeCalendar([event({start:'not-a-date'})],'2026-11-18','2028-01-01'));
 assert.throws(()=>normalizeCalendar([event({view_url:'https://other.invalid/'})],'2026-11-18','2028-01-01'));
});
test('a failed fetch keeps the previous calendar file intact',async()=>{
 const root=fs.mkdtempSync(path.join(os.tmpdir(),'tvs-calendar-'));
 try {fs.writeFileSync(path.join(root,'calendar-data.json'),'existing');
  await assert.rejects(buildCalendar({root,request:async()=>({ok:false,status:503})}));
  assert.equal(fs.readFileSync(path.join(root,'calendar-data.json'),'utf8'),'existing');
 }finally{fs.rmSync(root,{recursive:true,force:true});}
});
test('homepage shows January events and same-day events without timezone or month filtering loss',async()=>{
 const code=fs.readFileSync(path.join(__dirname,'../tvs.js'),'utf8');
 const body=code.slice(code.indexOf('async function displayUpcomingEvents()'),code.indexOf('function currentSpeaker('));
 class Element {constructor(){this.children=[];this.textContent='';}appendChild(e){this.children.push(e);}set innerHTML(v){this.markup=v;this.children=[];}get innerHTML(){return this.markup;}}
 const container=new Element();
 class Clock extends Date {constructor(...a){super(...(a.length?a:['2026-11-18T23:00:00Z']));}}
 const context={Date:Clock,Intl,console,fetch:async()=>({ok:true,json:async()=>({events:[
  {date:'2026-11-18',title:'Today',description:'',location:'Here'},
  {date:'2027-01-14',title:'STEAM Night',description:'',location:'School'},
  {date:'2026-11-17',title:'Past',description:'',location:''},
  {date:'2026-12-01',title:'Board Meeting',description:'',location:''}]})}),document:{head:new Element(),getElementById:()=>container,createElement:()=>new Element()}};
 vm.createContext(context);vm.runInContext(body,context);await context.displayUpcomingEvents();
 assert(container.markup.includes('Upcoming Club Events'));
 const table=container.children[0];assert.equal(table.children[1].children.length,2);
});
