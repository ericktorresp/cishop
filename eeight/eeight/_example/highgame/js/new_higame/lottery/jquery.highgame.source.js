/*
* lottery of highgame
*
* version: 1.0.0 (01/21/2010)
* @ jQuery v1.3 or later ,suggest use 1.4
*
* Copyright 2010 James [ jameskerr2009[at]gmail.com ] 
*  
*/
;(function($){//start

    //check the version, need 1.3 or later , suggest use 1.4
    if (/^1.2/.test($.fn.jquery) || /^1.1/.test($.fn.jquery)) {
    	alert('requires jQuery v1.3 or later !  You are using v' + $.fn.jquery);
    	return;
    }
    
    $.gameInit = function(opts){//整个购彩界面的初始化
        var ps = {//整个JS的初试化默认参数
            data_label      : [
                                /*{title:'前三直选',label:[
                                  {
                                   methodid : 1, //玩法ID
                                   name:'复式',//确认区连接显示的名字
                                   desc:'直选复式（鼠标录入）',//在radio后面的文字描述
                                   methoddesc:'从万、千、百位中选择一个3位数号码组成一注。',//对该玩法的文字描述
                                   selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'万位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                                           {title:'千位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1},
                                                           {title:'百位', no:'0|1|2|3|4|5|6|7|8|9', place:2, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              }, //选号区的形态描述,用于生成选号区
                                   show_str : 'X,X,X,-,-',//整个选择号码选择形式
                                   code_sp  : '',//单个号码之间的间隔方式
                                   data: ''//其他
                                  },
                                  {
                                    methodid : 1,
                                    name:'单式',
                                    desc:'直选单式（键盘录入）',
                                    methoddesc:'从万、千、百位中选择一个3位数号码组成一注。',
                                    selectarea:{type:'input'},
                                    show_str : 'X',
                                    code_sp : ' ',
                                    data:''
                                  },
                                  {
                                    methodid : 2,
                                    name:'和值',
                                    desc:'直选和值',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'直选和值', no:'0|1|2|3|4|5|6|7|8|9|10|11|12|13', place:0, cols:1},
                                                           {title:'', no:'14|15|16|17|18|19|20|21|22|23|24|25|26|27', place:0, cols:1}
                                                         ],
                                               isButton   : false
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                   }
                                                        ]},
                                {title:'后三直选',label:[
                                  {
                                    methodid : 3,
                                    name:'复式',
                                    desc:'直选复式（鼠标录入）',
                                    methoddesc:'从万、千、百位中选择一个3位数号码组成一注。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'百位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                                           {title:'十位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1},
                                                           {title:'个位', no:'0|1|2|3|4|5|6|7|8|9', place:2, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : '-,-,X,X,X',
                                    code_sp : '',
                                    data:''
                                  },
                                  {
                                    methodid : 3,
                                    name:'单式',
                                    desc:'直选单式（键盘录入）',
                                    methoddesc:'从万、千、百位中选择一个3位数号码组成一注。',
                                    selectarea:{type:'input'},
                                    show_str : 'X',
                                    code_sp : ' ',
                                    data:''
                                  },
                                  {
                                    methodid : 4,
                                    name:'和值',
                                    desc:'直选和值',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'直选和值', no:'0|1|2|3|4|5|6|7|8|9|10|11|12|13', place:0, cols:1},
                                                           {title:'', no:'14|15|16|17|18|19|20|21|22|23|24|25|26|27', place:0, cols:1}
                                                         ],
                                               isButton   : false
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  }
                                                        ]},
                                {title:'前三组选',label:[
                                  {
                                    methodid : 5,
                                    name:'组三',
                                    desc:'组三',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组三', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  },
                                  {
                                    methodid : 6,
                                    name:'组六',
                                    desc:'组六',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组六', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  },
                                  {
                                    methodid : 7,
                                    name:'混合',
                                    desc:'混合组选',
                                    methoddesc:'',
                                    selectarea:{type:'input'},
                                    show_str : 'X',
                                    code_sp : ' ',
                                    data:''
                                  },
                                  {
                                    methodid : 8,
                                    name:'和值',
                                    desc:'组选和值',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组选和值', no:'1|2|3|4|5|6|7|8|9|10|11|12|13', place:0, cols:1},
                                                           {title:'', no:'14|15|16|17|18|19|20|21|22|23|24|25|26', place:0, cols:1}
                                                         ],
                                               isButton   : false
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  }
                                                        ]},
                                {title:'后三组选',label:[
                                  {
                                    methodid : 9,
                                    name:'组三',
                                    desc:'组三',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组三', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  },
                                  {
                                    methodid : 10,
                                    name:'组六',
                                    desc:'组六',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组六', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  },
                                  {
                                    methodid : 11,
                                    name:'混合',
                                    desc:'混合组选',
                                    methoddesc:'',
                                    selectarea:{type:'input'},
                                    show_str : 'X',
                                    code_sp : ' ',
                                    data:''
                                  },
                                  {
                                    methodid : 12,
                                    name:'和值',
                                    desc:'组选和值',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组选和值', no:'1|2|3|4|5|6|7|8|9|10|11|12|13', place:0, cols:1},
                                                           {title:'', no:'14|15|16|17|18|19|20|21|22|23|24|25|26', place:0, cols:1}
                                                         ],
                                               isButton   : false
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  }
                                                        ]},
                                {title:'后三不定位',label:[
                                  {
                                    methodid : 13,
                                    name:'一码',
                                    desc:'一码不定位',
                                    methoddesc:'从0-9中任意选择一个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'不定位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5, ,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  },
                                  {
                                    methodid : 14,
                                    name:'二码',
                                    desc:'二码不定',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'不定位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  }
                                                        ]},
                                {title:'二码',label:[
                                  {
                                    methodid : 15,
                                    name:'前二直选',
                                    desc:'前二直选',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'万位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                                           {title:'千位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X,X,-,-,-',
                                    code_sp : '',
                                    data:''
                                  },
                                  {
                                    methodid : 16,
                                    name:'前二组选',
                                    desc:'前二组选',
                                    methoddesc:'从0-9中任意选择两个号码进行购买。',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组选', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  },
                                  {
                                    methodid : 17,
                                    name:'后二直选',
                                    desc:'后二直选',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'十位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                                           {title:'个位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : '-,-,-,X,X',
                                    code_sp : '',
                                    data:''
                                  },
                                  {
                                    methodid : 18,
                                    name:'后二组选',
                                    desc:'后二组选',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'组选', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X',
                                    code_sp : ',',
                                    data:''
                                  }
                                                        ]},
                                {title:'定位胆',label:[
                                  {
                                    methodid : 19,
                                    name:'',
                                    desc:'定位胆',
                                    methoddesc:'',
                                    selectarea:{
                                               type   : 'digital',
                                               layout : [
                                                           {title:'万位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                                           {title:'千位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1},
                                                           {title:'百位', no:'0|1|2|3|4|5|6|7|8|9', place:2, cols:1},
                                                           {title:'十位', no:'0|1|2|3|4|5|6|7|8|9', place:3, cols:1},
                                                           {title:'个位', no:'0|1|2|3|4|5|6|7|8|9', place:4, cols:1}
                                                          ],
                                               noBigIndex : 5,
                                               isButton   : true
                                              },
                                    show_str : 'X,X,X,X,X',
                                    code_sp : '',
                                    data:''
                                  }
                                                        ]},
                                {title:'五星大小单双',label:[
                                  {
                                    methodid : 20,
                                    name:'前二',
                                    desc:'前二大小单双',
                                    methoddesc:'对万位，千位的 (56789),小(01234),单(13579),双(02468)进行形态购买',
                                    selectarea:{
                                                type:'dxds',
                                                layout: [{title:'万位', no:'大|小|单|双', place:0, cols:1},
                                                         {title:'千位', no:'大|小|单|双', place:1, cols:1}]
                                              },
                                    show_str : 'X,X',
                                    code_sp : '',
                                    data:''
                                  },
                                  {
                                    methodid : 21,
                                    name:'后二',
                                    desc:'后二大小单双',
                                    methoddesc:'对百位，个位的 (56789),小(01234),单(13579),双(02468)进行形态购买',
                                    selectarea:{
                                                type:'dxds',
                                                layout: [{title:'十位', no:'大|小|单|双', place:0, cols:1},
                                                         {title:'个位', no:'大|小|单|双', place:1, cols:1}]
                                              },
                                    show_str : 'X,X',
                                    code_sp : '',
                                    data:''
                                  }
                                                        ]}*/
                              ],
            
            data_id : {
                        id_cur_issue    : '#current_issue',//装载当前期的ID
                        id_cur_end      : '#current_endtime',//装载当前期结束时间的ID
                        id_count_down   : '#count_down',//装载倒计时的ID
                        id_labelbox     : '#lt_big_label', //装载大标签的元素ID
                        id_smalllabel   : '#lt_samll_label',//装载小标签的元素ID
                        id_methoddesc   : '#lt_desc',//装载玩法描述的ID
                        id_methodhelp   : '#lt_help',//玩法帮助
                        id_helpdiv      : '#lt_help_div',//玩法帮助弹出框
                        id_selector     : '#lt_selector',//装载选号区的ID
                        id_sel_num      : '#lt_sel_nums',//装载选号区投注倍数的ID
                        id_sel_money    : '#lt_sel_money',//装载选号区投注金额的ID
                        id_sel_times    : '#lt_sel_times',//选号区倍数输入框ID
                        id_sel_insert   : '#lt_sel_insert',//添加按钮
                        id_sel_modes    : '#lt_sel_modes',//元角模式选择
                        id_cf_count     : '#lt_cf_count', //统计投注单数
                        id_cf_clear     : '#lt_cf_clear', //清空确认区数据的按钮ID
                        id_cf_content   : '#lt_cf_content',//装载确认区数据的TABLE的ID
                        id_cf_num       : '#lt_cf_nums',//装载确认区总投注注数的ID
                        id_cf_money     : '#lt_cf_money',//装载确认区总投注金额的ID
                        id_issues       : '#lt_issues',//装载起始期的ID
                        id_sendok       : '#lt_sendok',  //立即购买按钮
                        id_tra_if       : '#lt_trace_if',//是否追号的div
                        id_tra_ifb      : '#lt_trace_if_button',//是否追号的hidden input
                        id_tra_stop     : '#lt_trace_stop',//是否追中即停的checkbox ID
                        id_tra_box1     : '#lt_trace_box1',//装载整个追号内容的ID，主要是隐藏和显示
                        id_tra_box2     : '#lt_trace_box2',//装载整个追号内容的ID，主要是隐藏和显示
                        id_tra_today    : '#lt_trace_today',//今天按钮的ID
                        id_tra_tom      : '#lt_trace_tomorrow',//明天按钮的ID
                        id_tra_alct     : '#lt_trace_alcount',//装载可追号期数的ID
                        id_tra_label    : '#lt_trace_label',//装载同倍，翻倍，利润追号等元素的ID
                        id_tra_lhtml    : '#lt_trace_labelhtml',//装载同倍翻倍等标签所表示的内容
                        id_tra_ok       : '#lt_trace_ok',//立即生成按钮
                        id_tra_issues   : '#lt_trace_issues',//装载追号的一系列期数的ID
                        id_select       : '#times'//jack 增加的下拉框选择倍数ID
                    },
                         
            cur_issue : {issue:'20100210-001',endtime:'2010-02-10 09:10:00'},  //当前期
            issues    : {//所有的可追号期数集合
                         today:[
                                /*{issue:'20100210-001',endtime:'2010-02-10 09:10:00'},
                                {issue:'20100210-002',endtime:'2010-02-10 09:20:00'},
                                {issue:'20100210-003',endtime:'2010-02-10 09:30:00'},
                                {issue:'20100210-004',endtime:'2010-02-10 09:40:00'},
                                {issue:'20100210-005',endtime:'2010-02-10 09:50:00'},
                                {issue:'20100210-006',endtime:'2010-02-10 10:00:00'},
                                {issue:'20100210-007',endtime:'2010-02-10 10:10:00'},
                                {issue:'20100210-008',endtime:'2010-02-10 10:20:00'},
                                {issue:'20100210-009',endtime:'2010-02-10 10:30:00'},
                                {issue:'20100210-010',endtime:'2010-02-10 10:40:00'},
                                {issue:'20100210-011',endtime:'2010-02-10 10:50:00'}*/
                               ],
                         tomorrow: [
                                /*{issue:'20100211-001',endtime:'2010-02-10 09:10:00'},
                                {issue:'20100211-002',endtime:'2010-02-10 09:20:00'},
                                {issue:'20100211-003',endtime:'2010-02-10 09:30:00'},
                                {issue:'20100211-004',endtime:'2010-02-10 09:40:00'},
                                {issue:'20100211-005',endtime:'2010-02-10 09:50:00'},
                                {issue:'20100211-006',endtime:'2010-02-10 10:00:00'},
                                {issue:'20100211-007',endtime:'2010-02-10 10:10:00'},
                                {issue:'20100211-008',endtime:'2010-02-10 10:20:00'},
                                {issue:'20100211-009',endtime:'2010-02-10 10:30:00'},
                                {issue:'20100211-010',endtime:'2010-02-10 10:40:00'},
                                {issue:'20100211-011',endtime:'2010-02-10 10:50:00'},
                                {issue:'20100211-011',endtime:'2010-02-10 11:00:00'}*/
                               ]
                        },
            servertime : '2010-02-10 09:09:40',//服务器时间[与服务器同步]
            ajaxurl    : '',    //提交的URL地址,获取下一期的地址是后面加上flag=read,提交是后面加上flag=save
            lotteryid  : 1,//彩种ID
            ontimeout  : function(){},//时间结束后执行的函数
            onfinishbuy: function(){},//购买成功后调用的函数
            test : ''
        }
        opts = $.extend( {}, ps, opts || {} ); //根据参数初始化默认配置
        /*************************************全局参数配置 **************************/
        $.extend({
            lt_id_data : opts.data_id,
            lt_method_data : {},//当前所选择的玩法数据
            lt_method : {2:'ZX3',3:'ZXHZ',5:'ZX3',6:'ZXHZ',8:'ZUS',9:'ZUL',10:'HHZX',11:'ZUHZ',13:'ZUS',14:'ZUL',15:'HHZX',16:'ZUHZ',18:'BDW1',20:'BDW2',22:'ZX2',26:'ZU2',24:'ZX2',28:'ZU2',30:'DWD',31:'DWD',32:'DWD',33:'DWD',34:'DWD',36:'DXDS',38:'DXDS',
            89:'ZX3',92:'ZXHZ',102:'ZX3',103:'ZXHZ',104:'ZUS',105:'ZUL',106:'HHZX',107:'ZUHZ',108:'ZUS',109:'ZUL',110:'HHZX',111:'ZUHZ',112:'BDW1',113:'BDW2',114:'ZX2',115:'ZX2',116:'ZU2',117:'ZU2',118:'DWD',119:'DWD',120:'DWD',121:'DWD',122:'DWD',123:'DXDS',124:'DXDS',
            126:'ZX3',127:'ZXHZ',129:'ZX3',130:'ZXHZ',132:'ZUS',133:'ZUL',134:'HHZX',135:'ZUHZ',137:'ZUS',138:'ZUL',139:'HHZX',140:'ZUHZ',142:'BDW1',144:'BDW2',146:'ZX2',148:'ZX2',150:'ZU2',152:'ZU2',154:'DWD',155:'DWD',156:'DWD',157:'DWD',158:'DWD',160:'DXDS',162:'DXDS',
            265:'ZX3',266:'ZXHZ',268:'ZX3',269:'ZXHZ',271:'ZUS',272:'ZUL',273:'HHZX',274:'ZUHZ',276:'ZUS',277:'ZUL',278:'HHZX',279:'ZUHZ',281:'BDW1',283:'BDW2',285:'ZX2',287:'ZX2',289:'ZU2',291:'ZU2',293:'DWD',294:'DWD',295:'DWD',296:'DWD',297:'DWD',299:'DXDS',301:'DXDS',
            189:'ZX3',190:'ZXHZ',192:'ZUS',193:'ZUL',194:'HHZX',195:'ZUHZ',197:'BDW1',199:'ZX2',201:'ZX2',203:'ZU2',205:'ZU2',261:'DWD',262:'DWD',263:'DWD',
            220:'SDZX3',222:'SDZU3',224:'SDZX2',226:'SDZU2',228:'SDBDW',230:'SDDWD',231:'SDDWD',232:'SDDWD',234:'SDDDS',236:'SDCZW',238:'SDRX1',240:'SDRX2',243:'SDRX3',246:'SDRX4',249:'SDRX5',252:'SDRX6',255:'SDRX7',258:'SDRX8',
            303:'SDZX3',305:'SDZU3',307:'SDZX2',309:'SDZU2',311:'SDBDW',313:'SDDWD',314:'SDDWD',315:'SDDWD',317:'SDDDS',319:'SDCZW',321:'SDRX1',323:'SDRX2',325:'SDRX3',327:'SDRX4',329:'SDRX5',331:'SDRX6',333:'SDRX7',335:'SDRX8',
            337:'SDZX3',339:'SDZU3',341:'SDZX2',343:'SDZU2',345:'SDBDW',347:'SDDWD',348:'SDDWD',349:'SDDWD',351:'SDDDS',353:'SDCZW',355:'SDRX1',357:'SDRX2',359:'SDRX3',361:'SDRX4',363:'SDRX5',365:'SDRX6',367:'SDRX7',369:'SDRX8',
            393:'SDZX3',395:'SDZU3',397:'SDZX2',399:'SDZU2',401:'SDBDW',403:'SDDWD',404:'SDDWD',405:'SDDWD', 407:'SDDDS',409:'SDCZW',411:'SDRX1',413:'SDRX2',415:'SDRX3',417:'SDRX4',419:'SDRX5',421:'SDRX6',423:'SDRX7',425:'SDRX8'},
            lt_issues : opts.issues,//所有的可追号期的初始集合
            lt_ajaxurl: opts.ajaxurl,
            lt_lottid : opts.lotteryid,
            lt_total_nums : 0,//总投注注数
            lt_total_money: 0,//总投注金额[非追号]
            lt_time_leave : 0, //本期剩余时间
            lt_same_code  : [],//用于限制一个方法里不能投相同单
            lt_ontimeout  : opts.ontimeout,
            lt_onfinishbuy: opts.onfinishbuy,
            lt_trace_base : 0,//追号的基本金额.
            lt_submiting  : false,//是否正在提交表单
            lt_prizes   : [] //投注内容的奖金情况
        });
        ps = null;
        opts.data_id = null;
        opts.issues  = null;
        opts.ajaxurl = null;
        opts.lotteryid = null;
        if( $.browser.msie ){//&& /MSIE 6.0/.test(navigator.userAgent)
            CollectGarbage();//释放内存
        }
        //开始倒计时
        $($.lt_id_data.id_count_down).lt_timer(opts.servertime,opts.cur_issue.endtime);
        //装载模式选择
        $('<select name="lt_project_modes" id="lt_project_modes"></select>').appendTo($.lt_id_data.id_sel_modes);
        var bhtml = ''; //大标签HTML
        $.each(opts.data_label, function(i,n){//生成标签
            if(typeof(n)=='object'){
                if( i == 0 ){//第一个标签自动选择
                    bhtml += '<div value="'+i+'"><div class="menu_01a"></div><div class="menu_02a"><a href="javascript:">'+n.title+'</a></div><div class="menu_03a"></div></div>';
                    lt_smalllabel({//生成该标签下的小标签
                            title:n.title,
                            label:n.label });
                }else{
                    bhtml += '<div value="'+i+'"><div class="menu_01"></div><div class="menu_02"><a href="javascript:">'+n.title+'</a></div><div class="menu_03"></div></div>';
                }
            }
        });
        $bhtml = $(bhtml);
        $(bhtml).appendTo($.lt_id_data.id_labelbox);
        $($.lt_id_data.id_labelbox).children().click(function(){//切换标签
            if( $($(this).children()[0]).attr("class").indexOf('a')>=0 ){//如果已经是当前标签则不切换
                return;
            }
            $.each($($.lt_id_data.id_labelbox).children().children(), function(){
            	if($(this).attr('class').indexOf('a') >= 0)
            	{
            		var bbbbbb = $(this).attr('class').replace('a','');
            		$(this).removeAttr('class').addClass(bbbbbb);
            	}
            	else
            	{
            		return;  
            	}
            });
            $.each($(this).children(),function(i,n){
            	var cls = $(n).attr('class');
            	$(n).removeClass(cls).addClass(cls+'a');
            });
            var index = parseInt($(this).attr("value"),10);
            lt_smalllabel({
                            title:opts.data_label[index].title,
                            label:opts.data_label[index].label 
                         });
        });
        //写入当前期
        $($.lt_id_data.id_cur_issue).html(opts.cur_issue.issue);
        //写入当前期结束时间
        $($.lt_id_data.id_cur_end).html(opts.cur_issue.endtime);
        //生成并写入起始期内容
        var chtml = '<select name="lt_issue_start" id="lt_issue_start">';
        $.each($.lt_issues.today,function(i,n){
            chtml += '<option value="'+n.issue+'">'+n.issue+(n.issue==opts.cur_issue.issue?lot_lang.dec_s7:'')+'</option>';
        });
        var t = $.lt_issues.tomorrow.length-$.lt_issues.today.length;
        if( t > 0 ){//如果当天的期数小于每天的固定期数则继续增加显示
            for( i=0; i<t; i++ ){
                chtml += '<option value="'+$.lt_issues.tomorrow[i].issue+'">'+$.lt_issues.tomorrow[i].issue+'</option>';
            }
        }
        chtml += '</select><input type="hidden" name="lt_total_nums" id="lt_total_nums" value="0"><input type="hidden" name="lt_total_money" id="lt_total_money" value="0">';
        $(chtml).appendTo($.lt_id_data.id_issues);
        //确认区事件
        $("tr",$($.lt_id_data.id_cf_content)).live("mouseover",function(){//确认区行颜色变化效果
            $(this).children().addClass("temp");
        }).live("mouseout",function(){
            $(this).children().removeClass("temp");
        });
        $($.lt_id_data.id_cf_clear).click(function(){//清空按钮
            $.confirm(lot_lang.am_s5,function(){
                $.lt_total_nums  = 0;//总注数清零
                $.lt_total_money = 0;//总金额清零
                $.lt_trace_base  = 0;//追号金额基数清零
                $.lt_same_code   = [];//已在确认区的数据
                $($.lt_id_data.id_cf_num).html(0);//显示数据清零
                $($.lt_id_data.id_cf_money).html(0);//显示数据清零
                $($.lt_id_data.id_cf_count).html(0);//总投注项清零
                $($.lt_id_data.id_cf_content).children().empty();
                cleanTraceIssue();//清空追号区数据
            });
        });
        //追号区
        $($.lt_id_data.id_tra_if).lt_trace({issues:opts.issues});
        
        //确认投注按钮事件
        $($.lt_id_data.id_sendok).lt_ajaxSubmit();
        
        //帮助中心
        $($.lt_id_data.id_methodhelp).hover(function(){
            if($($.lt_id_data.id_helpdiv).html().length > 2){
                var offset = $(this).offset();
                if($($.lt_id_data.id_helpdiv).html().length > 30){
                    $($.lt_id_data.id_helpdiv).css({"width":"300px"});
                }else{
                    $($.lt_id_data.id_helpdiv).css({"width":($.browser.msie ? "300px" : "auto")});
                }
                $($.lt_id_data.id_helpdiv).css({"left":(offset.left+$(this).width()+2)+"px","top":(offset.top-20)+"px"}).show();
            }
        },function(){
            $($.lt_id_data.id_helpdiv).hide();
        });
        
    }
    
    var lt_smalllabel = function(opts){//动态载入小标签
        var ps = {title:'',label:[]};    //标签数据
        opts   = $.extend( {}, ps, opts || {} ); //根据参数初始化默认配置
        var html = '';
        var modelhtml = '';
        function addItem(o, t, v){
            var i = new Option(t, v);      
            o.options.add(i);
        }
        function SelectItem(obj,value){
            for(var i=0;i<obj.options.length;i++){
                if(obj.options[i].value == value){
                    obj.options[i].selected = true;
                    return true;
                }
            }
        }
        $.each(opts.label, function(i,n){
            if(typeof(n)=='object'){
                if( i > 0 && i % 4 == 0 ){//4个小标签一换行
                    html += '</div><div>';
                }
                if( i == 0 ){//第一个标签自动选择
                    html += '<input type="radio" name="smalllabel" value="'+i+'" checked="checked" />'+n.desc+'&nbsp;';
                    if( n.methoddesc.length >0 ){
                        $($.lt_id_data.id_methoddesc).html(n.methoddesc).parent().show();
                    }else{
                        $($.lt_id_data.id_methoddesc).parent().hide();
                    }
                    if( n.methodhelp && n.methodhelp.length > 0 ){
                        $($.lt_id_data.id_helpdiv).html(n.methodhelp);
                    }else{
                        $($.lt_id_data.id_helpdiv).html("");
                    }
                    lt_selcountback();//选号区的统计归零
                    $.lt_method_data = {
                                        methodid : n.methodid,
                                        title: opts.title,
                                        name : n.name,
                                        str  : n.show_str,
                                        prize: n.prize,
                                        modes: $.lt_method_data.modes ? $.lt_method_data.modes : {},
                                        sp   : n.code_sp
                                      };
                    $($.lt_id_data.id_selector).lt_selectarea(n.selectarea);//生成选号界面
                    //生成模式选择
                    //modelhtml = '<select name="lt_project_modes" id="lt_project_modes">';
                    //modelhtml = '';
                    selmodes  = getCookie("modes");
                    $("#lt_project_modes").empty();
                    //$("#lt_project_modes")[0].options.length ==0;
                    $.each(n.modes,function(j,m){
                        $.lt_method_data.modes[m.modeid]= {name:m.name,rate:Number(m.rate)};
                        //modelhtml += '<option value="'+m.modeid+'" '+(selmodes==m.modeid ? 'selected="selected"' : '')+' >&nbsp;&nbsp;'+m.name+'&nbsp;&nbsp;</option>';
                        addItem($("#lt_project_modes")[0],''+m.name+'',m.modeid);
                    });
                    SelectItem($("#lt_project_modes")[0],selmodes);
                    //$("#lt_project_modes").empty();
                    //$("#lt_project_modes")[0].options.length ==0;
                    //$(modelhtml).appendTo("#lt_project_modes");
                    /*modelhtml += '</select>';
                    $($.lt_id_data.id_sel_modes).empty();
                    $(modelhtml).appendTo($.lt_id_data.id_sel_modes);*/
                }else{
                    html += '<input type="radio" name="smalllabel" value="'+i+'" />'+n.desc+'&nbsp;';
                }
            }
            
        });
        html += '<input type="hidden">';
        $html = $('<div>'+html+'</div>');
        $($.lt_id_data.id_smalllabel).empty();
        $html.appendTo($.lt_id_data.id_smalllabel);
        if( opts.label.length == 1 ){
            $($.lt_id_data.id_smalllabel).empty();
        }
        $("input[name='smalllabel']:first").attr("checked",true).data("ischecked",'yes');//第一个标签自动选择[兼容各种浏览器]
        $("input[name='smalllabel']").click(function(){
            if( $(this).data("ischecked") == 'yes' ){//如果已经选择则无任何动作
                return;
            }
            var index = parseInt($(this).val(),10);
            if( opts.label[index].methoddesc.length >0 ){
                $($.lt_id_data.id_methoddesc).html(opts.label[index].methoddesc).parent().show();
            }else{
                $($.lt_id_data.id_methoddesc).parent().hide();
            }
            if( opts.label[index].methodhelp && opts.label[index].methodhelp.length>0 ){
                $($.lt_id_data.id_helpdiv).html(opts.label[index].methodhelp);
            }else{                $($.lt_id_data.id_helpdiv).html("");
            }
            lt_selcountback();//选号区的统计归零
            $.lt_method_data = {
                                methodid : opts.label[index].methodid,
                                title: opts.title,
                                name : opts.label[index].name,
                                str  : opts.label[index].show_str,
                                prize: opts.label[index].prize,
                                modes: $.lt_method_data.modes ? $.lt_method_data.modes : {},
                                sp   : opts.label[index].code_sp
                              };
            $("input[name='smalllabel']").removeData("ischecked");
            $(this).data("ischecked",'yes'); //标记为已选择
            $($.lt_id_data.id_selector).lt_selectarea(opts.label[index].selectarea);//生成选号界面
            //生成模式选择
            //modelhtml = '<select name="lt_project_modes" id="lt_project_modes">';
            //modelhtml = '';
            $("#lt_project_modes").empty();
            //$("#lt_project_modes")[0].options.length ==0;
            selmodes  = getCookie("modes");
            $.each(opts.label[index].modes,function(j,m){
                $.lt_method_data.modes[m.modeid]= {name:m.name,rate:Number(m.rate)};
                //modelhtml += '<option value="'+m.modeid+'" '+(selmodes==m.modeid ? 'selected="selected"' : '')+' >&nbsp;&nbsp;'+m.name+'&nbsp;&nbsp;</option>';
                addItem($("#lt_project_modes")[0],''+m.name+'',m.modeid);
            });
            SelectItem($("#lt_project_modes")[0],selmodes);
            //$("#lt_project_modes").empty();
            //$("#lt_project_modes")[0].options.length ==0;
            //$(modelhtml).appendTo("#lt_project_modes");
            /*modelhtml += '</select>';
            $($.lt_id_data.id_sel_modes).empty();
            $(modelhtml).appendTo($.lt_id_data.id_sel_modes);*/
        });
    };
    
    var lt_selcountback = function(){
        $($.lt_id_data.id_sel_times).val(1);
        $($.lt_id_data.id_sel_money).html(0);
        $($.lt_id_data.id_sel_num).html(0);
    };
    
    //选号区动态插入函数，可能是手动编辑
    $.fn.lt_selectarea = function( opts ){
        var ps = {//默认参数
                type   : 'digital', //选号，'input':输入型,'digital':数字选号型,'dxds':大小单双类型
                layout : [
                           {title:'百位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                           {title:'十位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1},
                           {title:'个位', no:'0|1|2|3|4|5|6|7|8|9', place:2, cols:1}
                          ],//数字型的号码排列
                noBigIndex : 5,    //前面多少个号码是小号,即大号是从多少个以后开始的
                isButton   : true,  //是否需要全大小奇偶清按钮
                imagedir   : './js/lottery/image/' //按钮图片文件夹位置
            };
        opts = $.extend( {}, ps, opts || {} ); //根据参数初始化默认配置
        var data_sel = [];//用户已选择或者已输入的数据
        var max_place= 0; //总共的选择型排列数
        var otype = opts.type.toLowerCase();    //类型全部转换为小写
        var methodname = $.lt_method[$.lt_method_data.methodid];//玩法的简写,如:'ZX3'
        var html  = '<div id="right_05"><table cellpadding="0" cellspacing="0" width="100%">';
        $("#right_03").css("display","block");
        $("#right_04").css("display","block");
        if( otype == 'input' ){//输入型，则载入输入型的数据
            var html  = '<div id="right_05_input"><table cellpadding="0" cellspacing="0" width="100%">';
            var tempdes    = '';
            switch( methodname ){
                case 'SDZX3' :
                case 'SDZU3' :
                case 'SDZX2' :
                case 'SDRX1' :
                case 'SDRX2' :
                case 'SDRX3' :
                case 'SDRX4' :
                case 'SDRX5' :
                case 'SDRX6' :
                case 'SDRX7' :
                case 'SDRX8' :
                case 'SDZU2' : tempdes = lot_lang.dec_s26; break;
                default      : tempdes = lot_lang.dec_s4; break;
            }
            $("#right_03").css("display","none");
            $("#right_04").css("display","none");
            html += '<tr><td align=center><textarea id="lt_write_box"></textarea><br>'+tempdes+'</td><td class="writebutbox" width="108px"><div class="botton15" id="lt_write_del">删除重复号</div><div class="botton15" id="lt_write_import" >导入文件</div><div class="botton15"  id="lt_write_empty" >清&nbsp;&nbsp;空</div><br></td><td width="40px"></td></tr>';
            data_sel[0] = []; //初始数据
            tempdes = null;
        }else if( otype == 'digital' ){//数字选号型
            $.each(opts.layout, function(i,n){
                if(typeof(n)=='object'){
                    n.place  = parseInt(n.place,10);
                    max_place = n.place > max_place ? n.place : max_place;
                    data_sel[n.place] = [];//初始数据
                    html += '<tr>';
                    if( n.cols > 0 ){//有标题
                        html += '<td rowspan="'+n.cols+'" class="'+(n.title.length<3 ? 'two' : (n.title.length>3 ? 'four' : 'three') )+'">';
                        if( n.title.length > 0 ){
                            html += '<div class="button2"><div>'+n.title+'</div></div>';
                        }
                        html += '</td>';
                    }
                    html += '<td><div class="right_02_11">';
                    numbers = n.no.split("|");
                    for( i=0; i<numbers.length; i++ ){
                        html += '<div name="lt_place_'+n.place+'" class="button1">'+numbers[i]+'</div>';
                    }
                    html += '</div></td><td><div class="right_02_11">';
                    if( opts.isButton == true ){
                        html += '<div name="all" class="button3">'+lot_lang.bt_sel_all+'</div><div class="button3" name="big">'+lot_lang.bt_sel_big+'</div><div class="button3" name="small">'+lot_lang.bt_sel_small+'</div><div class="button3" name="odd">'+lot_lang.bt_sel_odd+'</div><div class="button3" name="even">'+lot_lang.bt_sel_even+'</div><div class="selcleanbutton" name="clean">'+lot_lang.bt_sel_clean+'</div>';
                    }
                    html += '</div></td></tr>';
                    
                }
            });
        }else if( otype == 'dxds' ){//大小单双类型
            $.each(opts.layout, function(i,n){
                n.place  = parseInt(n.place,10);
                max_place = n.place > max_place ? n.place : max_place;
                data_sel[n.place] = [];//初始数据
                html += '<tr>';
                if( n.cols > 0 ){//有标题
                    html += '<td align="left" width="100px;">';
                    if( n.title.length > 0 ){
                        html += '<div class="button2">'+n.title+'</div>';
                    }
                    html += '</td>';
                }
                html += '<td><div class="right_02_11">';
                numbers = n.no.split("|");
                for( i=0; i<numbers.length; i++ ){
                    html += '<div name="lt_place_'+n.place+'" class="button1">'+numbers[i]+'</div>';
                }
                html += '<div class="selcleanbutton" name="clean">'+lot_lang.bt_sel_clean+'</div></div></td></tr>';
            });
        }else if( otype == 'dds' ){
            $.each(opts.layout, function(i,n){
                n.place  = parseInt(n.place,10);
                max_place = n.place > max_place ? n.place : max_place;
                data_sel[n.place] = [];//初始数据
                html += '<tr>';
                if( n.cols > 0 ){//有标题
                    html += '<td rowspan="'+n.cols+'" class="'+(n.title.length<3 ? 'two' : (n.title.length>3 ? 'four' : 'three') )+'">';
                    if( n.title.length > 0 ){
                        html += '<div class="seltitle"><div>'+n.title+'</div></div>';
                    }
                    html += '</td>';
                }
                html += '<td><div class="selddsbox">';
                numbers = n.no.split("|");
                temphtml= '';
                if( n.prize ){
                    tmpprize = n.prize.split(",");
                }
                for( i=0; i<numbers.length; i++ ){
                    html += '<div name="lt_place_'+n.place+'" class="button2 floatleft margin10">'+numbers[i]+'</div>';
                    if( n.prize ){
                        temphtml += '<span>'+$.lt_method_data.prize[parseInt(tmpprize[i],10)]+'</span>';
                    }
                }
                html += temphtml+'</div><td></tr>';
            });
        }
        html += '</table></div>';
        $html = $(html)
        $(this).empty();
        $html.appendTo(this);
        var me = this;
        var _SortNum = function(a,b){//数字大小排序
            if( otype != 'input' ){
                a = a.replace(/5单0双/g,0).replace(/4单1双/g,1).replace(/3单2双/g,2).replace(/2单3双/g,3).replace(/1单4双/g,4).replace(/0单5双/g,5);
                a = a.replace(/大/g,0).replace(/小/g,1).replace(/单/g,2).replace(/双/g,3).replace(/\s/g,"");
                b = b.replace(/5单0双/g,0).replace(/4单1双/g,1).replace(/3单2双/g,2).replace(/2单3双/g,3).replace(/1单4双/g,4).replace(/0单5双/g,5);
                b = b.replace(/大/g,0).replace(/小/g,1).replace(/单/g,2).replace(/双/g,3).replace(/\s/g,"");
            }
            a = parseInt(a,10);
            b = parseInt(b,10);
            if( isNaN(a) || isNaN(b) ){
                return true;
            }
            return (a-b);
        };
        /************************ 验证号码合法性以及计算单笔投注注数以及金额 ***********************/
        //===================输入型检测
        var _HHZXcheck = function(n,len){//混合组选合法号码检测，合法返回TRUE，非法返回FALSE,n号码，len号码长度
            if( len == 2 ){//两位
                var a = ['00','11','22','33','44','55','66','77','88','99'];
            }else{//三位[默认]
                var a = ['000','111','222','333','444','555','666','777','888','999'];
            }
            n     = n.toString();
            if( $.inArray(n,a) == -1 ){//不在非法列表中
                return true;
            }
            return false;
        };
        var _SDinputCheck = function(n,len){//山东十一运的手动输入型的检测[不能重复，只能是01-11的数字]
            t = n.split(" ");
            l = t.length;
            for( i=0; i<l; i++ ){
                if( Number(t[i]) > 11 || Number(t[i]) < 1 ){//超过指定范围
                    return false;
                }
                for( j=i+1; j<l; j++ ){
                    if( Number(t[i]) == Number(t[j]) ){//重复的号码
                        return false;
                    }
                }
            }
            return true;
        };
        //号码检测,l:号码长度,e是否返回非法号码，true是,false返回合法注数,fun对每个号码的附加检测,sort:是否对每个号码排序
        var _inputCheck_Num = function(l,e,fun,sort){
            var nums = data_sel[0].length;
            var error= [];
            var newsel=[];
            var partn= "";
            l = parseInt(l,10);
            switch(l){
                case 2 : partn= /^[0-9]{2}$/;break;
                case 5 : partn= /^[0-9\s]{5}$/;break;
                case 8 : partn= /^[0-9\s]{8}$/;break;
                case 11 : partn= /^[0-9\s]{11}$/;break;
                case 14 : partn= /^[0-9\s]{14}$/;break;
                case 17 : partn= /^[0-9\s]{17}$/;break;
                case 20 : partn= /^[0-9\s]{20}$/;break;
                case 23 : partn= /^[0-9\s]{23}$/;break;
                default: partn= /^[0-9]{3}$/;break;
            }
            fun = $.isFunction(fun) ? fun : function(s){return true;};
            $.each(data_sel[0],function(i,n){
                n = $.trim(n);
                if( partn.test(n) && fun(n,l) ){//合格号码
                    if( sort ){
                        if( n.indexOf(" ") == -1 ){
                            n = n.split("");
                            n.sort(_SortNum);
                            n = n.join("");
                        }else{
                            n = n.split(" ");
                            n.sort(_SortNum);
                            n = n.join(" ");
                        }
                    }
                    data_sel[0][i] = n;
                    newsel.push(n);
                }else{//不合格则注数减
                    if( n.length>0 ){
                        error.push(n);
                    }
                    nums = nums - 1;
                }
            });
            if( e == true ){
                data_sel[0] = newsel;
                return error;
            }
            return nums;
        };
        function checkNum(){//时时计算投注注数与金额等
            var nums  = 0, mname = $.lt_method[$.lt_method_data.methodid];//玩法的简写,如:'ZX3'
            var modes = parseInt($("#lt_project_modes").val(),10);//投注模式
            //01:验证号码合法性并计算注数
            if( otype == 'input' ){//输入框形式的检测
                if( data_sel[0].length > 0 ){//如果输入的有值
                    switch(mname){
                        case 'ZX3'  : nums = _inputCheck_Num(3,false); break;
                        case 'HHZX' : nums = _inputCheck_Num(3,false,_HHZXcheck,true); break;
                        case 'ZX2'  : nums = _inputCheck_Num(2,false); break;
                        case 'ZU2'  : nums = _inputCheck_Num(2,false,_HHZXcheck,true); break;
                        case 'SDZX3': nums = _inputCheck_Num(8,false,_SDinputCheck,false); break;
                        case 'SDZU3': nums = _inputCheck_Num(8,false,_SDinputCheck,true); break;
                        case 'SDZX2': nums = _inputCheck_Num(5,false,_SDinputCheck,false); break;
                        case 'SDZU2': nums = _inputCheck_Num(5,false,_SDinputCheck,true); break;
                        case 'SDRX1': nums = _inputCheck_Num(2,false,_SDinputCheck,false); break;
                        case 'SDRX2': nums = _inputCheck_Num(5,false,_SDinputCheck,true); break;
                        case 'SDRX3': nums = _inputCheck_Num(8,false,_SDinputCheck,true); break;
                        case 'SDRX4': nums = _inputCheck_Num(11,false,_SDinputCheck,true); break;
                        case 'SDRX5': nums = _inputCheck_Num(14,false,_SDinputCheck,true); break;
                        case 'SDRX6': nums = _inputCheck_Num(17,false,_SDinputCheck,true); break;
                        case 'SDRX7': nums = _inputCheck_Num(20,false,_SDinputCheck,true); break;
                        case 'SDRX8': nums = _inputCheck_Num(23,false,_SDinputCheck,true); break;
                        default   : break;
                    }
                }
            }else{//其他选择号码形式[暂时就数字型和大小单双]
                var tmp_nums = 1;
                switch(mname){//根据玩法分类不同做不同处理
                    case 'ZXHZ' :   //直选和值特殊算法
                                    var cc = {0:1,1:3,2:6,3:10,4:15,5:21,6:28,7:36,8:45,9:55,10:63,11:69,12:73,13:75,14:75,15:73,16:69,17:63,18:55,19:45,20:36,21:28,22:21,23:15,24:10,25:6,26:3,27:1};
                    case 'ZUHZ' :   //混合组选特殊算法
                                    if( mname == 'ZUHZ' ){
                                        cc = {1:1,2:2,3:2,4:4,5:5,6:6,7:8,8:10,9:11,10:13,11:14,12:14,13:15,14:15,15:14,16:14,17:13,18:11,19:10,20:8,21:6,22:5,23:4,24:2,25:2,26:1};
                                    }
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        for( j=0; j<s; j++ ){
                                            nums += cc[parseInt(data_sel[i][j],10)];
                                        }
                                    };break;
                    case 'ZUS'  :   //组三
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 1 ){//组三必须选两位或者以上
                                            nums += s*(s-1);
                                        }
                                    };break;
                    case 'ZUL'  :   //组六
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 2 ){//组六必须选三位或者以上
                                            nums += s*(s-1)*(s-2)/6;
                                        }
                                    };break;
                    case 'BDW2'  :  //二码不定位
                    case 'ZU2'   :  //2位组选
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 1 ){//二码不定位必须选两位或者以上
                                            nums += s*(s-1)/2;
                                        }
                                    };break;
                    case 'DWD'  :   //定位胆所有在一起特殊处理
                                    for( i=0; i<=max_place; i++ ){
                                        nums += data_sel[i].length;
                                    };break;
                    case 'SDZX3': //山东11运前三直选
                                    nums = 0;
                                    if( data_sel[0].length > 0 && data_sel[1].length > 0 && data_sel[2].length > 0 ){
                                        for( i=0; i<data_sel[0].length; i++ ){
                                            for( j=0; j<data_sel[1].length; j++ ){
                                                for( k=0; k<data_sel[2].length; k++ ){
                                                    if( data_sel[0][i] != data_sel[1][j] && data_sel[0][i] != data_sel[2][k] && data_sel[1][j] != data_sel[2][k] ){
                                                        nums++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                    case 'SDZU3': //山东11运前三组选
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 2 ){//组六必须选三位或者以上
                                            nums += s*(s-1)*(s-2)/6;
                                        }
                                    };break;
                    case 'SDZX2': //山动十一运前二直选
                                  nums = 0;
                                    if( data_sel[0].length > 0 && data_sel[1].length > 0 ){
                                        for( i=0; i<data_sel[0].length; i++ ){
                                            for( j=0; j<data_sel[1].length; j++ ){
                                                if( data_sel[0][i] != data_sel[1][j]){
                                                    nums++;
                                                }
                                            }
                                        }
                                    }
                                    break;
                    case 'SDZU2': //山东十一运前二组选
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 1 ){//组六必须选三位或者以上
                                            nums += s*(s-1)/2;
                                        }
                                    };break;
                    case 'SDBDW':
                    case 'SDDWD':
                    case 'SDDDS':
                    case 'SDCZW':
                    case 'SDRX1': //任选1中1
                                    for( i=0; i<=max_place; i++ ){
                                        nums += data_sel[i].length;
                                    };break;
                    case 'SDRX2': //任选2中2
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 1 ){//二码不定位必须选两位或者以上
                                            nums += s*(s-1)/2;
                                        }
                                    };break;
                    case 'SDRX3': //任选3中3
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 2 ){//必须选三位或者以上
                                            nums += s*(s-1)*(s-2)/6;
                                        }
                                    };break;
                    case 'SDRX4': //任选4中4
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 3 ){//必须选四位或者以上
                                            nums += s*(s-1)*(s-2)*(s-3)/24;
                                        }
                                    };break;
                    case 'SDRX5': //任选5中5
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 4 ){//必须选四位或者以上
                                            nums += s*(s-1)*(s-2)*(s-3)*(s-4)/120;
                                        }
                                    };break;
                    case 'SDRX6': //任选6中6
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 5 ){//必须选四位或者以上
                                            nums += s*(s-1)*(s-2)*(s-3)*(s-4)*(s-5)/720;
                                        }
                                    };break;
                    case 'SDRX7': //任选7中7
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 6 ){//必须选四位或者以上
                                            nums += s*(s-1)*(s-2)*(s-3)*(s-4)*(s-5)*(s-6)/5040;
                                        }
                                    };break;
                    case 'SDRX8': //任选8中8
                                    for( i=0; i<=max_place; i++ ){
                                        var s = data_sel[i].length;
                                        if( s > 7 ){//必须选四位或者以上
                                            nums += s*(s-1)*(s-2)*(s-3)*(s-4)*(s-5)*(s-6)*(s-7)/40320;
                                        }
                                    };break;
                    default     : //默认情况
                                    for( i=0; i<=max_place; i++ ){
                                        if( data_sel[i].length == 0 ){//有位置上没有选择
                                            tmp_nums = 0;
                                            break;break;
                                        }
                                        tmp_nums *= data_sel[i].length;
                                    }
                                    nums = tmp_nums;
                                    ;break;
                }
            }
            //03:计算金额
            var times = parseInt($($.lt_id_data.id_sel_times).val(),10);
            if( isNaN(times) )
            {
                times = 1;
                $($.lt_id_data.id_sel_times).val(1);
            }
            var money = Math.round(times * nums * 2 * ($.lt_method_data.modes[modes].rate * 1000))/1000;//倍数*注数*单价 * 模式
            money = isNaN(money) ? 0 : money;
            $($.lt_id_data.id_sel_num).html(nums);   //写入临时的注数
            $($.lt_id_data.id_sel_money).html(money);//写临时单笔价格
        };
        //重复号处理
        var dumpNum = function(isdeal){
            var l   = data_sel[0].length;
            var err = [];
            var news= []; //除去重复号后的结果
            if( l == 0 ){
                return err;
            }
            for( i=0; i<l; i++ ){
                if( $.inArray(data_sel[0][i],err) != -1 ){
                    continue;
                }
                for( j=i+1; j<l; j++ ){
                    if( data_sel[0][i] == data_sel[0][j] ){
                        err.push(data_sel[0][i]);
                        break;
                    }
                }
                news.push(data_sel[0][i]);
            }
            if( isdeal ){//如果是做删除重复号的处理
                data_sel[0] = news;
            }
            return err;
        };
        //输入框的字符串处理
        function _inptu_deal(){
            var s = $.trim($("#lt_write_box",$(me)).val());
            s     = $.trim(s.replace(/[^\s\r,;，；　０１２３４５６７８９0-9]/g,""));
            var m = s;
            switch( methodname ){
                case 'SDZX3' :
                case 'SDZU3' :
                case 'SDZX2' :
                case 'SDRX1' :
                case 'SDRX2' :
                case 'SDRX3' :
                case 'SDRX4' :
                case 'SDRX5' :
                case 'SDRX6' :
                case 'SDRX7' :
                case 'SDRX8' :
                case 'SDZU2' : s = s.replace(/[\r\n,;，；]/g,"|").replace(/(\|)+/g,"|"); break;
                default      : s = s.replace(/[\s\r,;，；　]/g,"|").replace(/(\|)+/g,"|"); break;
            }
            s = s.replace(/０/g,"0").replace(/１/g,"1").replace(/２/g,"2").replace(/３/g,"3").replace(/４/g,"4").replace(/５/g,"5").replace(/６/g,"6").replace(/７/g,"7").replace(/８/g,"8").replace(/９/g,"9");
            if( s == "" ){
            	  data_sel[0] = []; //清空数据
            }else{
            	  data_sel[0] = s.split("|");
            }
            return m;
        };
        /************************ 事件触发处理 ****************************/
        if( otype == 'input' ){//手动输入型处理
            $("#lt_write_del",$(me)).click(function(){//删除重复号
                var err = dumpNum(true);
                if( err.length > 0 ){//如果有重复号码
                    checkNum();
                    switch( methodname ){
                        case 'SDZX3' :
                        case 'SDZU3' :
                        case 'SDZX2' :
                        case 'SDRX1' :
				                case 'SDRX2' :
				                case 'SDRX3' :
				                case 'SDRX4' :
				                case 'SDRX5' :
				                case 'SDRX6' :
				                case 'SDRX7' :
				                case 'SDRX8' :
                        case 'SDZU2' : $("#lt_write_box",$(me)).val(data_sel[0].join(";"));
                                       $.alert(lot_lang.am_s3+'\r\n'+err.join(";"));
                                       break;
                        default      : $("#lt_write_box",$(me)).val(data_sel[0].join(" "));
                                       $.alert(lot_lang.am_s3+'\r\n'+err.join(" "));
                                       break;
                    }
                }else{
                    $.alert(lot_lang.am_s4);
                }
            });
            $("#lt_write_import",$(me)).click(function(){//载入文件处理
                $.ajaxUploadUI({
              title    : lot_lang.dec_s27,
        			url      : './js/dialogUI/fileupload.php',//服务端处理的文件
        			loadok   : lot_lang.dec_s28,
        			filetype : ['txt','csv'],//允许载入的文件类型
        			success  : function(data){ $("#lt_write_box",$(me)).val(data).change(); },//数据处理
        			onfinish : function(){$("#lt_write_box",$(me)).focus();}
        		});
            });
            $("#lt_write_box",$(me)).change(function(){//输入框时时变动处理
                var s = _inptu_deal();
                $(this).val(s);
                checkNum();
             }).keyup(function(){
                _inptu_deal();
                checkNum();
            });
            $("#lt_write_empty",$(me)).click(function(){//清空处理
                data_sel[0] = []; //清空数据
                $("#lt_write_box",$(me)).val("");
                checkNum();
            });
        }
        
        //选中号码处理
        function selectNum( obj, isButton ){
            if( $.trim($(obj).attr("class")) == 'selected' ){//如果本身是已选中，则不做任何处理
                return;
            }
            $(obj).attr("class","selected");//样式改变为选中
            place = Number($(obj).attr("name").replace("lt_place_",""));
            var number = $.trim($(obj).html());
            data_sel[place].push(number);//加入到数组中
            if( !isButton ){//如果不是按钮触发则进行统计，按钮的统一进行统计
                checkNum();
            }
        };
        //取消选中号码处理
        function unSelectNum( obj, isButton ){
            if( $.trim($(obj).attr("class")) != 'selected' ){//如果本身是未选中，则不做任何处理
                return;
            }
            $(obj).attr("class","button1");//样式改变为未选中
            place = Number($(obj).attr("name").replace("lt_place_",""));
            var number = $.trim($(obj).html());
            data_sel[place] = $.grep(data_sel[place],function(n,i){//从选中数组中删除取消的号码
                return n == number;
            },true);
            if( !isButton ){//如果不是按钮触发则进行统计，按钮的统一进行统计
                checkNum();
            }
        };
        //选择与取消号码选择交替变化
        function changeNoCss(obj){
            if( $.trim($(obj).attr("class")) == 'selected' ){//如果本身是已选中，则做取消
                unSelectNum(obj,false);
            }else{
                selectNum(obj,false);
            }
        };
        //选择奇数号码
        function selectOdd(obj){
            if( Number($(obj).html()) % 2 == 1 ){
                 selectNum(obj,true);
            }else{
                 unSelectNum(obj,true);
            }
        };
        //选择偶数号码
        function selectEven(obj){
            if( Number($(obj).html()) % 2 == 0 ){
                 selectNum(obj,true);
            }else{
                 unSelectNum(obj,true);
            }
        };
        //选则大号
        function selectBig(i,obj){
            if( i >= opts.noBigIndex ){
                selectNum(obj,true);
            }else{
                unSelectNum(obj,true);
            }
        };
        //选择小号
        function selectSmall(i,obj){
            if( i < opts.noBigIndex ){
                selectNum(obj,true);
            }else{
                unSelectNum(obj,true);
            }
        };
        //设置号码事件
        $(this).find("div[name^='lt_place_']").click(function(){
            changeNoCss(this);
        });
        //全大小单双清按钮的动作行为处理
        if( opts.isButton == true ){//如果有这几个按钮才做处理
            $("div[class='button3']",$(this)).click(function(){//清除处理
            	var td = $(this).parent().parent().parent().children()[1];
            	td = $(td);
                switch( $(this).attr('name') ){
                    case 'all'   :
                                 $.each(td.children().children(),function(i,n){
                                    selectNum(n,true);
                                 });
                                 break;
                    case 'big'   :
                                 $.each(td.children().children(),function(i,n){
                                    selectBig(i,n);
                                 });break;
                    case 'small' :
                                 $.each(td.children().children(),function(i,n){
                                    selectSmall(i,n);
                                 });break;
                    case 'odd'   :
                                 $.each(td.children().children(),function(i,n){
                                    selectOdd(n);
                                 });break;
                    case 'even'  :
                                 $.each(td.children().children(),function(i,n){
                                    selectEven(n);
                                 });break;
                    default : break;
                }
                checkNum();
            });
            $("div[class='selcleanbutton']",$(this)).click(function(){//清除处理
            	var td = $(this).parent().parent().parent().children()[1];
            	td = $(td);
            	$.each(td.children().children(),function(i,n){
                    unSelectNum(n,true);
                });
                checkNum();
            });
        }else if( otype == 'dxds' ){//或者玩法为大小单双即有清按钮的处理
            $("div[class='selcleanbutton']",$(this)).click(function(){
                $.each($(this).parent().children(":first").children(),function(i,n){
                    unSelectNum(n,true);
                });
                checkNum();
            });
        }
        //倍数输入处理事件
        $($.lt_id_data.id_sel_times).keyup(function(){
            var times = $(this).val().replace(/[^0-9]/g,"").substring(0,5);
            $(this).val( times );
            if( times == "" ){
                times = 0;
            }else{
                times = parseInt(times,10);//取整倍数
            }
            var nums  = parseInt($($.lt_id_data.id_sel_num).html(),10);//投注注数取整
            var modes = parseInt($("#lt_project_modes").val(),10);//投注模式
            var money = Math.round(times * nums * 2 * ($.lt_method_data.modes[modes].rate * 1000))/1000;//倍数*注数*单价 * 模式
            money = isNaN(money) ? 0 : money;
            $($.lt_id_data.id_sel_money).html(money);
        });
        $($.lt_id_data.id_select).change(function(){
            $($.lt_id_data.id_sel_times).val($(this).val()).keyup();
        });
        //模式变换处理事件
        $("#lt_project_modes").change(function(){
            var nums  = parseInt($($.lt_id_data.id_sel_num).html(),10);//投注注数取整
            var times = parseInt($($.lt_id_data.id_sel_times).val(),10);//投注倍数取整
            var modes = parseInt($("#lt_project_modes").val(),10);//投注模式
            var money = Math.round(times * nums * 2 * ($.lt_method_data.modes[modes].rate * 1000))/1000;//倍数*注数*单价 * 模式
            money = isNaN(money) ? 0 : money;
            $($.lt_id_data.id_sel_money).html(money);
        });
        //添加按钮
        $($.lt_id_data.id_sel_insert).unbind("click").click(function(){
            var nums  = parseInt($($.lt_id_data.id_sel_num).html(),10);//投注注数取整
            var times = parseInt($($.lt_id_data.id_sel_times).val(),10);//投注倍数取整
            var modes = parseInt($("#lt_project_modes").val(),10);//投注模式
            var money = Math.round(times * nums * 2 * ($.lt_method_data.modes[modes].rate * 1000))/1000;//倍数*注数*单价 * 模式
            var mid   = $.lt_method_data.methodid;
            if( isNaN(nums) || isNaN(times) || isNaN(money) || money <= 0 ){//如果没有任何投注内容
                $.alert(otype == 'input' ? lot_lang.am_s29 : lot_lang.am_s19);
                return;
            }
            if( otype == 'input' ){//如果是输入型，则检测号码合法性，以及是否存在重复号
                var mname = $.lt_method[mid];//玩法的简写,如:'ZX3'
                var error = [];
                var edump = [];
                var ermsg = "";
                //检测重复号，并除去重复号
                edump = dumpNum(true);
                if( edump.length >0 ){//有重复号
                    ermsg += lot_lang.em_s2+'\n'+edump.join(", ")+"\n";
                    checkNum();//重新统计
                    nums  = parseInt($($.lt_id_data.id_sel_num).html(),10);//投注注数取整
                    money = Math.round(times * nums * 2 * ($.lt_method_data.modes[modes].rate * 1000))/1000;//倍数*注数*单价*模式
                }
                switch(mname){//根据类型不同做不同检测
                    case 'ZX3'  : error = _inputCheck_Num(3,true); break;
                    case 'HHZX' : error = _inputCheck_Num(3,true,_HHZXcheck,true); break;
                    case 'ZX2'  : error = _inputCheck_Num(2,true); break;
                    case 'ZU2'  : error = _inputCheck_Num(2,true,_HHZXcheck,true); break;
                    case 'SDZX3': error = _inputCheck_Num(8,true,_SDinputCheck,false); break;
                    case 'SDZU3': error = _inputCheck_Num(8,true,_SDinputCheck,true); break;
                    case 'SDZX2': error = _inputCheck_Num(5,true,_SDinputCheck,false); break;
                    case 'SDZU2': error = _inputCheck_Num(5,true,_SDinputCheck,true); break;
                    case 'SDRX1': error = _inputCheck_Num(2,true,_SDinputCheck,false); break;
                    case 'SDRX2': error = _inputCheck_Num(5,true,_SDinputCheck,true); break;
                    case 'SDRX3': error = _inputCheck_Num(8,true,_SDinputCheck,true); break;
                    case 'SDRX4': error = _inputCheck_Num(11,true,_SDinputCheck,true); break;
                    case 'SDRX5': error = _inputCheck_Num(14,true,_SDinputCheck,true); break;
                    case 'SDRX6': error = _inputCheck_Num(17,true,_SDinputCheck,true); break;
                    case 'SDRX7': error = _inputCheck_Num(20,true,_SDinputCheck,true); break;
                    case 'SDRX8': error = _inputCheck_Num(23,true,_SDinputCheck,true); break;
                    default     : break;
                }
                if( error.length > 0 ){//如果存在错误的号码，则提示
                    ermsg += lot_lang.em_s1+'\n'+error.join(", ")+"\n";
                }
                
                if( ermsg.length > 1 ){
                    $.alert(ermsg);
                }
            }
            var nos = $.lt_method_data.str;
            var serverdata = "{'type':'"+otype+"','methodid':"+mid+",'codes':'";
            var temp = [];
            for( i=0; i<data_sel.length; i++ ){
                nos = nos.replace('X',data_sel[i].sort(_SortNum).join($.lt_method_data.sp));
                temp.push( data_sel[i].sort(_SortNum).join("&") );
            }
            if( nos.length > 40 ){
                var nohtml = nos.substring(0,37)+'...<span class="open">'+lot_lang.dec_s5+'</span>';
            }else{
                var nohtml = nos;
            }
            //判断是否有重复相同的单
            if( $.lt_same_code[mid] != undefined && $.lt_same_code[mid][modes] != undefined && $.lt_same_code[mid][modes].length > 0 ){
                    if( $.inArray(temp.join("|"),$.lt_same_code[mid][modes]) != -1 ){//存在相同的
                        $.alert(lot_lang.am_s28);
                        return false;
                    }
            }
            nohtml  = '['+$.lt_method_data.title+'_'+$.lt_method_data.name+'] '+nohtml;
            noshtml = '['+$.lt_method_data.title+'_'+$.lt_method_data.name+'] '+nos.substring(0,37);
            serverdata += temp.join("|")+"','nums':"+nums+",'times':"+times+",'money':"+money+",'mode':"+modes+",'desc':'"+noshtml+"'}";
            var cfhtml = '<tr><th align="left" height="20px">'+nohtml+'</th><td align="center">'+$.lt_method_data.modes[modes].name+'</td><td align="center">'+nums+lot_lang.dec_s1+'</td><td align="center">'+times+lot_lang.dec_s2+'</td><td align="center">'+money+lot_lang.dec_s3+'</td><td class="del" align="center" width="20px"><span>X</span><input type="hidden" name="lt_project[]" value="'+serverdata+'" /></td></tr>';
            var $cfhtml = $(cfhtml);
            $cfhtml.prependTo($.lt_id_data.id_cf_content);
            //详情查看
            $('span',$cfhtml).filter(".open").click(function(){
                var mme = this;
                var $h = $('<font>'+lot_lang.dec_s5+'</font><span class="close">['+lot_lang.dec_s6+']</span><br /><textarea class="code" readonly="readonly">'+nos+'</textarea>');
                $(this).openFloat($h,"more");
                $("span",$(this).parent()).filter(".close").click(function(){
                    $(mme).closeFloat();
                });
            });
            
            $.lt_total_nums  += nums;//总注数增加
            $.lt_total_money += money;//总金额增加
            $.lt_total_money  = Math.round($.lt_total_money*1000)/1000;
            basemoney         = Math.round(nums * 2 * ($.lt_method_data.modes[modes].rate * 1000))/1000;//注数*单价 * 模式
            $.lt_trace_base   = Math.round(($.lt_trace_base+basemoney)*1000)/1000;
            $($.lt_id_data.id_cf_num).html($.lt_total_nums);//更新总注数显示
            $($.lt_id_data.id_cf_money).html($.lt_total_money);//更新总金额显示
            $($.lt_id_data.id_cf_count).html(parseInt($($.lt_id_data.id_cf_count).html(),10)+1);//总投注项加1
            //计算奖金，并且判断是否支持利润率追号
            var pc = 0;
            var pz = 0;
            $.each($.lt_method_data.prize,function(i,n){
                n = isNaN(Number(n)) ? 0 : Number(n);
                pz = pz > n ? pz : n;
                pc++;
            });
            if( pc != 1 ){
                pz = 0;
            }
            pz = Math.round( pz * ($.lt_method_data.modes[modes].rate * 1000))/1000;
            $cfhtml.data('data',{methodid:mid,nums:nums,money:money,modes:modes,basemoney:basemoney,prize:pz,code:temp.join("|")});
            //把投注内容记录到临时数组中，用于判断是否有重复
            if( $.lt_same_code[mid] == undefined ){
                    $.lt_same_code[mid] = [];
            }
            if( $.lt_same_code[mid][modes] == undefined ){
                    $.lt_same_code[mid][modes] = [];
            }
            $.lt_same_code[mid][modes].push(temp.join("|"));
            $('td',$cfhtml).filter(".del").find("span").css("cursor",'pointer').attr("title",lot_lang.dec_s24).click(function(){
                var n = $cfhtml.data('data').nums;
                var m = $cfhtml.data('data').money;
                var b = $cfhtml.data('data').basemoney;
                var c = $cfhtml.data('data').code;
                var d = $cfhtml.data('data').methodid;
                var f = $cfhtml.data('data').modes;
                var i = null;
                //移除临时数组中该投注内容，用于判断是否有重复
                $.each($.lt_same_code[d][f],function(k,code){
                    if( code == c ){
                        i = k;
                    }
                });
                if( i != null ){
                    $.lt_same_code[d][f].splice(i,1);
                }else{
                    $.alert(lot_lang.am_s27);
                    return;
                }
                $.lt_total_nums  -= n;//总注数减少
                $.lt_total_money -= m;//总金额减少
                $.lt_total_money = Math.round($.lt_total_money*1000)/1000;
                $.lt_trace_base  = Math.round(($.lt_trace_base-b)*1000)/1000;
                $(this).parent().parent().remove();
                $($.lt_id_data.id_cf_num).html($.lt_total_nums);//更新总注数显示
                $($.lt_id_data.id_cf_money).html($.lt_total_money);//更新总金额显示
                $($.lt_id_data.id_cf_count).html(parseInt($($.lt_id_data.id_cf_count).html(),10)-1);//总投注项减1
                cleanTraceIssue();//清空追号区数据
            });
            //把所选模式存入cookie里面
            SetCookie('modes',modes,86400);
            //成功添加以后清空选号区数据
            for( i=0; i<data_sel.length; i++ ){//清空已选择数据
                data_sel[i] = [];
            }
            if( otype == 'input' ){//清空所有显示的数据
                $("#lt_write_box",$(me)).val("");
            }
            else if( otype == 'digital' || otype == 'dxds' || otype == 'dds' ){
            	
                $.each($("div",$(me)).filter(".selected"),function(i,n){
                    $(this).removeClass("selected").addClass("button1");
                });
            }
            //还原倍数为1倍
            $($.lt_id_data.id_sel_times).val(1);
            checkNum();
            //清空追号区数据
            cleanTraceIssue();
        });
    };
    
    //追号区
    $.fn.lt_trace = function(){
        var t_type  = 'margin';//追号形式[利润率:margin,同倍:same,翻倍:diff]
        $.extend({
            lt_trace_issue: 0,//总追号期数
            lt_trace_money: 0//总追号金额
            });
        var t_count = $.lt_issues.tomorrow.length;//可追号期数
        var t_nowpos= 0;//当前起始期在追号列表的位置[超过该位置的就不在处理,优化等待时间]
        //装载可追号期数
        $($.lt_id_data.id_tra_alct).html(t_count);
        //装载同倍、翻倍标签
		
        var htmllabel = '<div id="button111">'+lot_lang.dec_s13+'</div><div id="button12">'+lot_lang.dec_s10+'</div><div id="button13">'+lot_lang.dec_s11+'</div>';
        var htmltext  = '<td align="left">追号计划：&nbsp;<span id="lt_margin_html">'+lot_lang.dec_s14+'&nbsp;<input class="input02" name="lt_trace_times_margin" type="text" id="lt_trace_times_margin" value="1" />&nbsp;'+lot_lang.dec_s29+'&nbsp;<input class="input02" name="lt_trace_margin" type="text" id="lt_trace_margin" value="50" />&nbsp;%&nbsp;</span>';
        htmltext += '<span id="lt_sametime_html" style="display:none;">'+lot_lang.dec_s14+'&nbsp;<input name="lt_trace_times_same" type="text" class="input022" id="lt_trace_times_same" value="1" /></span>';
        htmltext += '<span id="lt_difftime_html" style="display:none;">'+lot_lang.dec_s17+'&nbsp;<input name="lt_trace_diff" type="text"  value="1" class="input02" id="lt_trace_diff" />&nbsp;'+lot_lang.dec_s18+'　'+lot_lang.dec_s2+''+lot_lang.dec_s19+' <input name="lt_trace_times_diff" type="text" id="lt_trace_times_diff" value="2" class="input02" /></span>';
        htmltext += ''+lot_lang.dec_s15+':&nbsp;<input name="lt_trace_count_input" type="text" id="lt_trace_count_input" style="width:24px" value="10" /><input type="hidden" id="lt_trace_money" name="lt_trace_money" value="0" /></td>';
        
        $(htmllabel).appendTo($.lt_id_data.id_tra_label);
        $(htmltext).appendTo($.lt_id_data.id_tra_lhtml);
        $('#button111').click(function(){//利润率
            if( $(this).attr("class") != "temp" ){
                $(this).attr("id","button111");
                $('#button121').attr("id","button12");
                $('#button131').attr("id","button13");
                $('#lt_margin_html').show();
                $('#lt_sametime_html').hide();
                $('#lt_difftime_html').hide();
                t_type = 'margin';
            }
        });
        $('#button12').click(function(){//同倍
            if( $(this).attr("class") != "temp" ){
                $(this).attr("id","button121");
                $('#button111').attr("id","button11");
                $('#button131').attr("id","button13");
                $('#lt_margin_html').hide();
                $('#lt_sametime_html').show();
                $('#lt_difftime_html').hide();
                t_type = 'same';
            }
        });
        $('#button13').click(function(){//翻倍
            if( $(this).attr("class") != "temp" ){
                $(this).attr("id","button131");
                $('#button111').attr("id","button11");
                $('#button121').attr("id","button12");
                $('#lt_margin_html').hide();
                $('#lt_sametime_html').hide();
                $('#lt_difftime_html').show();
                t_type = 'diff';
            }
        });
        function upTraceCount(){//更新追号总期数和总金额
            $('#lt_trace_count').html($.lt_trace_issue);
            $('#lt_trace_hmoney').html(JsRound($.lt_trace_money,2,true));
            $('#lt_trace_money').val($.lt_trace_money);
        }
        
        //标签内的输入框键盘事件
        $("input",$($.lt_id_data.id_tra_lhtml)).keyup(function(){
            $(this).val( Number($(this).val().replace(/[^0-9]/g,"0")) );
        });
        //追号期快捷选择事件
        $("#lt_trace_qissueno").change(function(){
            var t=0;
            if($(this).val() == 'all' ){//全部可追号奖期
                t = parseInt($($.lt_id_data.id_tra_alct).html(),10);
            }else{
                t = parseInt($(this).val(),10);
            }
            t = isNaN(t) ? 0 : t;
            $("#lt_trace_count_input").val(t);
        });
        
        
        //装载追号的期号列表
        var issueshtml = '<table cellspacing="1" cellpadding="3" id="lt_trace_issues_today" width="100%">';
        $.each($.lt_issues.today,function(i,n){
            issueshtml += '<tr id="tr_trace_'+n.issue+'"><td align="center" bgcolor="#aeaeae"><input  id="checkbox2" type="checkbox" name="lt_trace_issues[]" value="'+n.issue+'" /></td><td align="center" bgcolor="#dad9d9">'+n.issue+'</td><td align="center" bgcolor="#dad9d9"><input name="lt_trace_times_'+n.issue+'" type="text" class="input02" value="0" disabled/>'+lot_lang.dec_s2+'</td><td align="center" bgcolor="#dad9d9">'+lot_lang.dec_s20+'<span id="lt_trace_money_'+n.issue+'">0.00</span></td><td align="center" bgcolor="#dad9d9">'+n.endtime+'</td></tr>';
        });
        issueshtml += '</table><table cellspacing="1" cellpadding="3" id="lt_trace_issues_tom" width="100%">';
        var t = $.lt_issues.tomorrow.length-$.lt_issues.today.length;
        $.each($.lt_issues.tomorrow,function(i,n){
        	if (i < t ){
        	issueshtml += '<tr id="tr_trace_'+n.issue+'"><td align="center" bgcolor="#aeaeae"><input  id="checkbox2" type="checkbox" name="lt_trace_issues[]" value="'+n.issue+'" /></td><td align="center" bgcolor="#dad9d9">'+n.issue+'</td><td align="center" bgcolor="#dad9d9" ><input name="lt_trace_times_'+n.issue+'" type="text" class="input02" value="0" disabled/>'+lot_lang.dec_s2+'</td><td align="center" bgcolor="#dad9d9">'+lot_lang.dec_s20+'<span id="lt_trace_money_'+n.issue+'">0.00</span></td><td align="center" bgcolor="#dad9d9">'+n.endtime+'</td></tr>';
        	}
        });
        issueshtml += '</table>';
        
        $(issueshtml).appendTo($.lt_id_data.id_tra_issues);
        function changeIssueCheck(obj){//选中或者取消某期
            var money = $.lt_trace_base;
            var $j = $(obj).closest("tr");
            if( $(obj).attr("checked") == true ){//选中
                $j.find("input[name^='lt_trace_times_']").val(1).attr("disabled",false).data("times",1);
                $j.find("span[id^='lt_trace_money_']").html(JsRound(money,2,true));
                $.lt_trace_issue++;
                $.lt_trace_money += money;
            }else{  //取消选中
                var t =$j.find("input[name^='lt_trace_times_']").val();
                $j.find("input[name^='lt_trace_times_']").val(0).attr("disabled",true).data("times",0);
                $j.find("span[id^='lt_trace_money_']").html('0.00');
                $.lt_trace_issue--;
                $.lt_trace_money -= money*parseInt(t,10);
            }
            $.lt_trace_money = JsRound($.lt_trace_money,2);
            upTraceCount();
        };
        $("input[name^='lt_trace_times_']",$($.lt_id_data.id_tra_issues)).keyup(function(){//每期的倍数变动
            var v = Number($(this).val().replace(/[^0-9]/g,"0"));
            $.lt_trace_money += $.lt_trace_base*(v-$(this).data('times'));
            upTraceCount();
            $(this).val(v).data("times",v);
            $(this).closest("tr").find("span[id^='lt_trace_money_']").html(JsRound($.lt_trace_base*v,2,true));
        });
        $(":checkbox",$.lt_id_data.id_tra_issues).click(function(){//取消与选择某期
            changeIssueCheck(this);
        });
        $("tr",$($.lt_id_data.id_tra_issues)).live("mouseover",function(){
            $(this).children().addClass("temp");
        }).live("mouseout",function(){
            if( $(this).find(":checkbox").attr("checked") == false ){
                $(this).children().removeClass("temp");
            }
        });
        var _initTraceByIssue = function(){//根据起始期的不同重新加载追号区
            var st_issue = $("#lt_issue_start").val();
            cleanTraceIssue();//清空追号方案
            var isshow   = false;//是否已经开始显示
            var acount   = 0;//不可追号期统计
            var loop     = 0;//循环次数
            var mins     = t_nowpos;//开始处理的位置[初始为上次变更的位置]
            var maxe     = t_nowpos;//结束处理的位置[初始为上次变更的位置]
            $.each($.lt_issues.today,function(i,n){
                loop++;
                if( isshow == false && st_issue == n.issue ){//如果选择的期数为当前期，则开始显示
                    isshow = true;
                    $($.lt_id_data.id_tra_today).click();
                }
                if( isshow == false ){
                    acount++;
                    maxe = Math.max(maxe,acount);//取最大的位置
                }else{
                    mins = Math.min(mins,acount);//取最小位置
                }
                if( loop >= mins && loop <= maxe ){//如果没有超过要处理的最大数，则继续处理
                    if( isshow == true ){//显示
                        $("#tr_trace_"+n.issue,$($.lt_id_data.id_tra_issues)).show();
                    }else{//隐藏
                        $("#tr_trace_"+n.issue,$($.lt_id_data.id_tra_issues)).hide();
                    }
                }
                if( loop > maxe ){//超过则退出不再处理
                    return false;
                }
            });
            $.each($.lt_issues.tomorrow,function(i,n){
                loop++;
                if( isshow == false && st_issue == n.issue ){//如果选择的期数为当前期，则开始显示
                    isshow = true;
                    $($.lt_id_data.id_tra_tom).click();
                }
                if( isshow == false ){
                    acount++;
                    maxe = Math.max(maxe,acount);
                }else{
                    mins = Math.min(mins,acount);//取最小位置
                }
                if( loop >= mins && loop <= maxe ){//如果没有超过要处理的最大数，则继续处理
                    if( isshow == true ){//显示
                        $("#tr_trace_"+n.issue,$($.lt_id_data.id_tra_issues)).show();
                    }else{//隐藏
                        $("#tr_trace_"+n.issue,$($.lt_id_data.id_tra_issues)).hide();
                    }
                }
                if( loop > maxe ){//超过则退出不再处理
                    return false;
                }
            });
            //更新可追号期数
            t_count = $.lt_issues.tomorrow.length - acount;
            if($("#lt_trace_qissueno").val()=='all'){
                $("#lt_trace_count_input").val(t_count);
            }
            t_nowpos = acount;
            $($.lt_id_data.id_tra_alct).html(t_count);
            //更新追号总期数和总金额
            $.lt_trace_issue = 0;
            $.lt_trace_money = 0;
            upTraceCount();
        };
        //起始期变动对追号区的影响
        $("#lt_issue_start").change(function(){
            if( $($.lt_id_data.id_tra_if).hasClass("clicked") == true ){//如果是选择了追号的情况才更新追号区
                _initTraceByIssue();
            }
        });
        //是否追号选择处理
        $($.lt_id_data.id_tra_if).click(function(){
            if( $($.lt_id_data.id_tra_if).hasClass('clicked') == false ){
                //检测是否有投注内容
                if( $.lt_total_nums <= 0 ){
                    $.alert(lot_lang.am_s7);
                    return;
                }
                $($.lt_id_data.id_tra_stop).attr("disabled",false).attr("checked",true);
                $(this).addClass("clicked");
                $($.lt_id_data.id_tra_box1).show();
                $($.lt_id_data.id_tra_box2).show();
                $($.lt_id_data.id_tra_ifb).val('yes');
                _initTraceByIssue();
            }else{
                $($.lt_id_data.id_tra_stop).attr("disabled",true).attr("checked",false);
                $($.lt_id_data.id_tra_box1).hide();
                $($.lt_id_data.id_tra_box2).hide();
                $($.lt_id_data.id_tra_ifb).val('no');
                $(this).removeClass("clicked");
            }
        });
        //今天明天标签切换
        $($.lt_id_data.id_tra_today).click(function(){//今天
            if( $(this).attr("class") != "selected" ){
                $(this).attr("class","selected");
                $($.lt_id_data.id_tra_tom).attr("class","");
                $("#lt_trace_issues_today").show();
                $("#lt_trace_issues_tom").hide();
            }
        });
        $($.lt_id_data.id_tra_tom).click(function(){//明天
            if( $(this).attr("class") != "selected" ){
                $(this).attr("class","selected");
                $($.lt_id_data.id_tra_today).attr("class","");
                $("#lt_trace_issues_today").hide();
                $("#lt_trace_issues_tom").show();
            }
        });
        //根据利润率计算当期需要的倍数[起始倍数，利润率，单倍购买金额，历史购买金额，单倍奖金],返回倍数
        var computeByMargin = function(s,m,b,o,p){
            s = s ? parseInt(s,10) : 0;
            m = m ? parseInt(m,10) : 0;
            b = b ? Number(b) : 0;
            o = o ? Number(o) : 0;
            p = p ? Number(p) : 0;
            var t = 0;
            if( b > 0 ){
                if( m > 0 ){
                    t = Math.ceil( ((m/100+1)*o)/(p-(b*(m/100+1))) );
                }else{//无利润率
                    t = 1;
                }
                if( t < s ){//如果最小倍数小于起始倍数，则使用起始倍数
                    t = s;
                }
            }
            return t;
        };
        //立即生成按钮
        $($.lt_id_data.id_tra_ok).click(function(){
            var c = parseInt($.lt_total_nums,10);//总投注注数
            if( c <= 0 ){//无投注内容
                $.alert(lot_lang.am_s7);
                return false;
            }
            var p = 0;//奖金
            if( t_type == 'margin' ){//如果为利润率追号则首先检测是否支持
                var marmt = 0;
                var marmd = 0;
                var martype =0;//利润率支持情况，0:支持,1:玩法不支持，2:多玩法，3:多圆角模式
                $.each($('tr',$($.lt_id_data.id_cf_content)),function(i,n){
                    if( marmt != 0 && marmt != $(n).data('data').methodid ){
                        martype = 2;
                        return false;
                    }else{
                        marmt = $(n).data('data').methodid;
                    }
                    if( marmd != 0 && marmd != $(n).data('data').modes ){
                        martype = 3;
                        return false;
                    }else{
                        marmd = $(n).data('data').modes;
                    }
                    if( $(n).data('data').prize <= 0 ){
                        martype = 1;
                        return false;
                    }else{
                        p = $(n).data('data').prize;
                    }
                });
                if( martype == 1 ){
                    $.alert(lot_lang.am_s32);
                    return false;
                }else if( martype == 2 ){
                    $.alert(lot_lang.am_s31);
                    return false;
                }else if( martype == 3 ){
                    $.alert(lot_lang.am_s33);
                    return false;
                }
            }
            var ic = parseInt($("#lt_trace_count_input").val(),10);//追号总期数
            ic = isNaN(ic) ? 0 : ic;
            if( ic <= 0 ){//期数没有填
                $.alert(lot_lang.am_s8);
                return false;
            }
            if( ic > t_count ){//超过可追号期数
                $.alert(lot_lang.am_s9);
                $("#lt_trace_count_input").val(t_count);
                return false;
            }
            var times = parseInt($("#lt_trace_times_"+t_type).val(),10);//倍数，当前追号方式里的倍数
            times = isNaN(times) ? 0 : times;
            if( times <= 0 ){
                $.alert(lot_lang.am_s10);
                return false;
            }
            times = isNaN(times) ? 0 : times;
            var td = [];//根据用户填写的条件生成的每期数据
            var tm = 0;//生成后的总金额
            var msg='';//提示信息
            if( t_type == 'same' ){
                var m = $.lt_trace_base*times;//每期金额
                tm = m*ic;//总金额=每期金额*期数
                for( var i=0; i<ic; i++ ){
                    td.push({times:times,money:m});
                }
                msg = lot_lang.am_s12.replace("[times]",times);
            }else if( t_type == 'diff' ){
                var d = parseInt($("#lt_trace_diff").val(),10);//相隔期数
                d = isNaN(d) ? 0 : d;
                if( d <= 0 ){
                    $.alert(lot_lang.am_s11);
                    return false;
                }
                var m = $.lt_trace_base;//每期金额的初始值
                var t = 1;//起始倍数为1
                for( var i=0; i<ic; i++ ){
                    if( i!= 0 && (i%d) == 0  ){
                        t *= times;
                    }
                    td.push({times:t,money:m*t});
                    tm += m*t;
                }
                msg = lot_lang.am_s13.replace("[step]",d).replace("[times]",times);
            }else if( t_type == 'margin' ){//利润追号
                var e = parseInt($("#lt_trace_margin").val(),10);//最低利润率
                e = isNaN(e) ? 0 : e;
                if( e <= 0 ){
                    $.alert(lot_lang.am_s30);
                    return false;
                }
                var m = $.lt_trace_base;//每期金额的初始值
                if( e >= ((p*100/m)-100) ){
                    $.alert(lot_lang.am_s30);
                    return false;
                }
                var t = 0;//返回的倍数
                for( var i=0; i<ic; i++ ){
                    t = computeByMargin(times,e,m,tm,p);
                    td.push({times:t,money:m*t});
                    tm += m*t;
                }
                msg = lot_lang.am_s34.replace("[margin]",e).replace("[times]",times);
            }
            msg += lot_lang.am_s14.replace("[count]",ic);
            $.confirm(msg,function(){
                cleanTraceIssue();//清空以前的追号方案
                var $s = $("tr:visible",$($.lt_id_data.id_tra_issues));
                for( i=0; i<ic; i++ ){
                    $($s[i]).find(":checkbox").attr("checked",true);
                    $($s[i]).find("input[name^='lt_trace_times_']").val(td[i].times).attr("disabled",false).data("times",td[i].times);
                    $($s[i]).find("span[id^='lt_trace_money_']").html(JsRound(td[i].money,2,true));
                    $($s[i]).children().addClass("tmp");
                }
                $.lt_trace_issue = ic;
                $.lt_trace_money = tm;
                upTraceCount();
            },'','',300);
        });
    }
    
    //清空追号方案
    var cleanTraceIssue =function(){
        $("input[name^='lt_trace_issues']",$($.lt_id_data.id_tra_issues)).attr("checked",false);
        $("input[name^='lt_trace_times_']",$($.lt_id_data.id_tra_issues)).val(0).attr("disabled",true);
        $("span[id^='lt_trace_money_']",$($.lt_id_data.id_tra_issues)).html('0.00');                
        $("td",$($.lt_id_data.id_tra_issues)).removeClass("selected");
        $('#lt_trace_hmoney').html(0);
        $('#lt_trace_money').val(0);
        $('#lt_trace_count').html(0);
        $.lt_trace_issue = 0;
        $.lt_trace_money = 0;
    };
    
    //倒计时
    $.fn.lt_timer = function(start,end){//服务器开始时间，服务器结束时间
        var me = this;
        if( start == "" || end == "" ){
            $.lt_time_leave = 0;
        }else{
            $.lt_time_leave = (format(end).getTime()-format(start).getTime())/1000;//总秒数
        }
        function fftime(n){
            return Number(n)<10 ? ""+0+Number(n) : Number(n); 
        }
        function format(dateStr){//格式化时间
            return new Date(dateStr.replace(/[\-\u4e00-\u9fa5]/g, "/"));
        }
        function diff(t){//根据时间差返回相隔时间
            return t>0 ? {
    			day : Math.floor(t/86400),
    			hour : Math.floor(t%86400/3600),
    			minute : Math.floor(t%3600/60),
    			second : Math.floor(t%60)
    		} : {day:0,hour:0,minute:0,second:0};
        }
        var timerno = window.setInterval(function(){
            if($.lt_time_leave > 0 && ($.lt_time_leave % 240 == 0 || $.lt_time_leave == 60 )){//每隔4分钟以及最后一分钟重新读取服务器时间
                $.ajax({
                        type: 'POST',
                        URL : $.lt_ajaxurl,
                        timeout : 30000,
                        data: "lotteryid="+$.lt_lottid+"&issue="+$($.lt_id_data.id_cur_issue).html()+"&flag=gettime",
                        success : function(data){//成功
                            data = parseInt(data,10);
                            data = isNaN(data) ? 0 : data;
                            data = data <= 0 ? 0 : data;
                            $.lt_time_leave = data;
                        }
                });
            }
            if( $.lt_time_leave <= 0 ){//结束
                clearInterval(timerno);
                if( $.lt_submiting == false ){//如果没有正在提交数据则弹出对话框,否则主动权交给提交表单
                    $.unblockUI({fadeInTime: 0, fadeOutTime: 0});
                    $.confirm(lot_lang.am_s15,function(){//确定
                        $.lt_reset(false);
                        $.lt_ontimeout();
                    },function(){//取消
                        $.lt_reset(true);
                        $.lt_ontimeout();
                    });
                }
            }
            var oDate = diff($.lt_time_leave--);
            $(me).html(""+(oDate.day>0 ? oDate.day+(lot_lang.dec_s21)+" " : "")+fftime(oDate.hour)+":"+fftime(oDate.minute)+":"+fftime(oDate.second));
        },1000);
    };
	
	//根据投单完成和本期销售时间结束，进行重新更新整个投注界面
	$.lt_reset = function(iskeep){
	    if( iskeep && iskeep === true ){
            iskeep = true;
        }else{
            iskeep = false;
        }
        if( $.lt_time_leave <= 0 ){//本期结束后的刷新
            //01:刷新选号区
            if( iskeep == false ){
                $(":radio:checked",$($.lt_id_data.id_smalllabel)).removeData("ischecked").click();
            }
            //02:刷新确认区
            if( iskeep == false ){
                $.lt_total_nums  = 0;//总注数清零
                $.lt_total_money = 0;//总金额清零
                $.lt_trace_base  = 0;//追号基础数据
                $.lt_same_code   = [];//已在确认区的数据
                $($.lt_id_data.id_cf_num).html(0);//显示数据清零
                $($.lt_id_data.id_cf_money).html(0);//显示数据清零
                $($.lt_id_data.id_cf_content).children().empty();
                $($.lt_id_data.id_cf_count).html(0);
                $("#times").attr('selected');
            }
            //读取新数据刷新必须刷新的内容
            $.ajax({
                type: 'POST',
                URL : $.lt_ajaxurl,
                data: "lotteryid="+$.lt_lottid+"&flag=read",
                success : function(data){//成功
                                if( data.length <= 0 ){
                                    $.alert(lot_lang.am_s16);
                                    return false;
                                }
                                var partn = /<script.*>.*<\/script>/;
                                if( partn.test(data) ){
                                    alert(lot_lang.am_s17);
        							top.location.href="../?controller=default";
        							return false;
                                }
                                if( data == "empty" ){
                                    alert(lot_lang.am_s18);
                                    window.location.href="./?controller=default&action=start";
                                    return false;
                                }
                                eval("data="+data);
                                //03:刷新当前期的信息
                                $($.lt_id_data.id_cur_issue).html(data.issue);
                                $($.lt_id_data.id_cur_end).html(data.saleend);
                                //04:重新开始计时
                                $($.lt_id_data.id_count_down).lt_timer(data.nowtime, data.saleend);
                                var l = $.lt_issues.today.length;
                                //05:更新起始期
                                while(true){
                                    if( data.issue == $.lt_issues.today.shift().issue ){
                                        break;
                                    }
                                }
                                $.lt_issues.today.unshift({issue:data.issue,endtime:data.saleend});
                                //重新生成并写入起始期内容
                                //var chtml = '<select name="lt_issue_start" id="lt_issue_start">';
                                var chtml = '';
                                $.each($.lt_issues.today,function(i,n){
                                    chtml += '<option value="'+n.issue+'">'+n.issue+(n.issue==data.issue?lot_lang.dec_s7:'')+'</option>';
                                });
                                var t = $.lt_issues.tomorrow.length-$.lt_issues.today.length;
                                if( t > 0 ){//如果当天的期数小于每天的固定期数则继续增加显示
                                    for( i=0; i<t; i++ ){
                                        chtml += '<option value="'+$.lt_issues.tomorrow[i].issue+'">'+$.lt_issues.tomorrow[i].issue+'</option>';
                                    }
                                }
                                /*chtml += '</select>';
                                $("#lt_issue_start").remove();
                                $(chtml).appendTo($.lt_id_data.id_issues);*/
                                $("#lt_issue_start").empty();
                                $(chtml).appendTo("#lt_issue_start");
                                //06:更新可追号期数
                                t_count = $.lt_issues.tomorrow.length;
                                $($.lt_id_data.id_tra_alct).html(t_count);
                                //07:更新追号数据
                                cleanTraceIssue();//清空追号区数据
                                while(true){//删除追号列表里已经过期的数据
                                    $j = $("tr:first",$("#lt_trace_issues_today"));
                                    if($j.length <= 0){
                                        break;
                                    }
                                    if( $j.find(":checkbox").val() == data.issue ){
                                        break;
                                    }
                                    $j.remove();
                                }
                          },
                error : function(){//失败
                    $.alert(lot_lang.am_s16);
                    cleanTraceIssue();//清空追号区数据
                    return false;
                }
            });
        }else{//提交表单成功后的刷新
            //01:刷新选号区
            if( iskeep == false ){
                $(":radio:checked",$($.lt_id_data.id_smalllabel)).removeData("ischecked").click();
            }
            //02:刷新确认区
            if( iskeep == false ){
                $.lt_total_nums  = 0;//总注数清零
                $.lt_total_money = 0;//总金额清零
                $.lt_trace_base  = 0;//追号基数
                $.lt_same_code   = [];//已在确认区的数据
                $($.lt_id_data.id_cf_num).html(0);//显示数据清零
                $($.lt_id_data.id_cf_money).html(0);//显示数据清零
                $($.lt_id_data.id_cf_content).children().empty();
                $($.lt_id_data.id_cf_count).html(0);
            }
            //03:刷新追号区
            if( iskeep == false ){
                cleanTraceIssue();//清空追号区数据
            }
        }
	};
	//提交表单
	$.fn.lt_ajaxSubmit = function(){
	    var me = this;
	    $(this).click(function(){
	        if( checkTimeOut() == false ){
	            return;
	        }
	        $.lt_submiting = true;//倒计时提示的主动权转移到这里
	        var istrace = $($.lt_id_data.id_tra_if).hasClass("clicked");
            if( $.lt_total_nums <= 0 || $.lt_total_money <= 0 ){//检查是否有投注内容
                $.lt_submiting = false;
                $.alert(lot_lang.am_s6);
                return;
            }
            if( istrace == true ){
	            if( $.lt_trace_issue <= 0 || $.lt_trace_money <= 0 ){//检查是否有追号内容
	                $.lt_submiting = false;
	                $.alert(lot_lang.am_s20);
                    return;
	            }
	            var terr = "";
	            $("input[name^='lt_trace_issues']:checked",$($.lt_id_data.id_tra_issues)).each(function(){
	                if( Number($(this).closest("tr").find("input[name^='lt_trace_times_']").val()) <= 0 ){
	                    terr += $(this).val()+'\n';
	                }
	            });
	            if( terr.length > 0 ){//有错误倍数的奖期
	                $.lt_submiting = false;
	                $.alert(lot_lang.am_s21.replace("[errorIssue]",terr));
                    return;
	            }
	        }
            if( istrace == true ){
                var msg = lot_lang.am_s14.replace("[count]",$.lt_trace_issue);
            }else{
                var msg = lot_lang.dec_s8.replace("[issue]",$("#lt_issue_start").val());
            }
            msg += '<div class="floatarea" style="height:150px;">';
            var modesmsg = [];
            var modes=0;
            $.each($('tr',$($.lt_id_data.id_cf_content)),function(i,n){
                modes = $(n).data('data').modes;
                if( modesmsg[modes] == undefined ){
                    modesmsg[modes] = [];
                }
                modesmsg[modes].push($("th",n).html().replace(lot_lang.dec_s5,"")+"\n");
            });
            $.each(modesmsg,function(i,n){
                if( $.lt_method_data.modes[i] != undefined && n != undefined && n.length>0 ){
                    msg += '<strong>'+$.lt_method_data.modes[i].name+"</strong>\n"+n.join("");
                }
            });
            msg += '</div>';
            msg += lot_lang.dec_s9+': '+(istrace==true ? $.lt_trace_money : $.lt_total_money)+' '+lot_lang.dec_s3;
            $.confirm(msg,function(){//点确定[提交]
            	
                if( checkTimeOut() == false ){//正式提交前再检查以下时间
                    $.lt_submiting = false;
    	            return;
    	        }
    	        $("#lt_total_nums").val($.lt_total_nums);
    	        $("#lt_total_money").val($.lt_total_money);
                ajaxSubmit();
            },function(){//点取消
                $.lt_submiting = false;
                return checkTimeOut();
            },'',400);
        });
        //检查时间是否结束，然后做处理
        function checkTimeOut(){
            if( $.lt_time_leave <= 0 ){//结束
                $.confirm(lot_lang.am_s15,function(){//确定
                    $.lt_reset(false);
                    $.lt_ontimeout();
                },function(){//取消
                    $.lt_reset(true);
                    $.lt_ontimeout();
                });
                return false;
            }else{
                return true;
            }
        };
        //ajax提交表单
        function ajaxSubmit(){
            $.blockUI({
            message: lot_lang.am_s22,
            overlayCSS: {backgroundColor: '#FFFFFF',opacity: 0.5,cursor:'wait'}
            });
            var form = $(me).closest("form");
            $.ajax({
                type: 'POST',
                url : $.lt_ajaxurl,
                timeout : 30000,
                data: $(form).serialize(),
                success: function(data){
//                        alert(data); return false;
                        $.unblockUI({fadeInTime: 0, fadeOutTime: 0});
                        $.lt_submiting = false;
                        //return false;
                        if( data.length <= 0 ){
                            $.alert(lot_lang.am_s16);
                            return false;
                        }
                        var partn = /<script.*>.*<\/script>/;
                        if( partn.test(data) ){
                            alert(lot_lang.am_s17);
							top.location.href="../?controller=default";
							return false;
                        }
                        if( data == "success" ){//购买成功
                            $.alert(lot_lang.am_s24,lot_lang.dec_s25,function(){
                                if( checkTimeOut() == true ){//时间未结束
                                    $.lt_reset();
                                }
                                $.lt_onfinishbuy();
                            });
                            return false;
                        }else{//购买失败提示
                            eval("data = "+ data +";");
                            if( data.stats == 'error' ){//错误
                                $.alert(data.data,'',function(){
                                    return checkTimeOut();
                                });
                                return false;
                            }
                            if( data.stats == 'fail' ){//有失败的
                                msg  = lot_lang.am_s25.replace("[success]",data.data.success).replace("[fail]",data.data.fail);
                                msg += '<div class="floatarea" style="height:100px;">';
                                $.each(data.data.content,function(i,n){
                                    msg += n+"\n";
                                });
                                msg += '</div>';
                                msg += lot_lang.am_s26;
                                $.confirm(msg,function(){//点确定[清空]
                                    if( checkTimeOut() == true ){//时间未结束
                                        $.lt_reset();
                                    }
                                    $.lt_onfinishbuy();
                                },function(){//点取消
                                    return checkTimeOut();
                                    $.lt_onfinishbuy();
                                },'',400);
                            }
                        }
                },
                error: function(){
                        $.lt_submiting = false;
                        $.unblockUI({fadeInTime: 0, fadeOutTime: 0});
                        $.alert(lot_lang.am_s23,'',checkTimeOut);
                     }
            });
        };
        
	};
	
})(jQuery);