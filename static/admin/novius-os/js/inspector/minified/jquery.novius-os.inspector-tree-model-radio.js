/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */
define("jquery-nos-inspector-tree-model-radio",["jquery","jquery-nos-treegrid"],function(a){a.fn.extend({nosInspectorTreeModelRadio:function(b){b=b||{};return this.each(function(){var d=a(this).css({height:b.height||"150px",width:b.width||""}),j=d.attr("id"),f=d.find("table"),c=d.closest(".nos-dispatcher, body").on("contextChange",function(){e();if(b.contextChange){f.nostreegrid("option","treeOptions",{context:c.data("nosContext")||""})}}),i=false,h=a('<input type="hidden" />').attr({name:b.input_name,value:a.isPlainObject(b.selected)&&b.selected.id?b.selected.id:""}).appendTo(d),e=function(){if(b.reloadEvent){d.nosUnlistenEvent("inspector"+j);var k={name:b.reloadEvent};if(c.data("nosContext")){k.context=c.data("nosContext")}d.nosListenEvent(k,function(){f.nostreegrid("reload")},"inspector"+j)}},g=function(){e();var k=a.extend(true,{},b.treeOptions||{});if(!k.context){k.context=c.data("nosContext")||""}f.nostreegrid({sortable:false,movable:false,urlJson:b.urlJson,treeColumnIndex:1,treeOptions:k,preOpen:b.selected||{},columnsAutogenerationMode:"none",scrollMode:"auto",cellStyleFormatter:function(l){if(l.$cell.is("td")){l.$cell.removeClass("ui-state-highlight")}},rowStyleFormatter:function(l){if(l.type==a.wijmo.wijgrid.rowType.header){l.$rows.hide()}},currentCellChanged:function(m){var n=a(m.target).nostreegrid("currentCell").row(),l=n?n.data:false;if(l&&i){b.selected.id=l._id;n.$rows.find(":radio[value="+b.selected.id+"]").prop("checked",true).triggerHandler("click")}},rendering:function(){i=false},rendered:function(){i=true;f.css("height","auto");if(a.isPlainObject(b.selected)&&b.selected.id){var m=d.find(":radio[value="+b.selected.id+"]").prop("checked",true),l=f.data("nos-nostreegrid");l._view()._getSuperPanel().scrollChildIntoView(m)}},columns:b.columns})};b.columns.unshift({allowMoving:false,allowSizing:false,width:35,ensurePxWidth:true,cellFormatter:function(k){if(a.isPlainObject(k.row.data)){a('<input type="radio" />').attr({name:b.input_name+"fake",value:k.row.data._id}).click(function(){b.selected={id:k.row.data._id,model:k.row.data._model};h.val(k.row.data._id);a(this).trigger("selectionChanged",k.row.data)}).appendTo(k.$container);return true}}});f.css({height:"100%",width:"100%"});f.nosOnShow("one",g)})}});return a});