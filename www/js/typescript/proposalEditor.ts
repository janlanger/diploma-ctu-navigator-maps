/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 24.3.13
 * Time: 22:49
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
	export class ProposalEditor extends Mapping.BasicMap {

		constructor(private mapElement:Element, private options) {
			super(mapElement, options);
		}

		public initialize() {
			super.initialize();
			this.registerEvents();
		}

		private registerEvents() {
			if (this.options.proposals) {
				$.each(this.options.proposals, (index, item) => {
					$("#" + index).click(() => {
						var element = $("#modalTemplate");
						$("#modal-header", element).text("Návrh od " + item.author + " z " + item.date + ":");
						var content = "Komentář uživatele: <em>" + item.comment + "</em><br><br>";
						if (item.specification.nodes && item.specification.nodes.length > 0) {
							content += "<strong>Body:</strong><ul>";
							for (var i = 0; i < item.specification.nodes.length; i++) {
								content += "<li>" + this.getNodeChangeTextual(item.specification.nodes[i]) + "</li>";
							}
							content += "</ul>";
						}
						if (item.specification.paths && item.specification.paths.length > 0) {
							content += "<strong>Cesty:</strong> ";
							var added = 0;
							var deleted = 0;
							for(var i = 0; i< item.specification.paths.length; i++) {
								if(item.specification.paths[i].deleted) {
									deleted++;
								} else {
									added++;
								}
							}
							if(added > 0)
								content += "<b>" + added + "x</b> nová ";
							if(deleted > 0)
								content += "<b>"+ deleted +"x</b> odstraněná";
						}
						$("#modal-content", element).html(content);
						element.modal({show: true});

						return false;
					});
				});
			}
		}

		private getNodeChangeTextual(nodeChange) {
			var description = "";
			if (nodeChange.original && !nodeChange.deleted) {
				description = "Změna bodu:";
				description += "<ul><li>" + this.nodeChangeDesc(nodeChange.original, nodeChange.properties).join("</li><li>") + "</li></ul>";
			}
			if (nodeChange.deleted) {
				description = "Odstranění bodu";
				description += "<ul><li>" + this.nodeChangeDesc(nodeChange.original).join("</li><li>") + "</li></ul>";
			}
			else if (!nodeChange.original) {
				description = "Nový bod:";
				description += "<ul><li>" + this.nodeChangeDesc(nodeChange.properties).join("</li><li>") + "</li></ul>";
			}
			return description;
		}

		private nodeChangeDesc(original, newNode = "") {
			var x = [];
			$.each(original, (index, item) => {
				var y = "";
				if (index == "id") return;
				if (newNode && item == newNode[index]) return;
				if (newNode) {
					if (index == "gpsCoordinates") {
						y += "<b>Změna pozice</b>";
					} else  {
						var newOne = newNode[index];
						if (index == "type") {
							item = this.readableNodeType(item);
							newOne = this.readableNodeType(newOne);
						}
						y += "<b>" + this.readableIndex(index) + ":</b> " + (item == null ? "prázdný" : item) + "->" + newOne;
					}
				} else {
					if (index == "gpsCoordinates" || item == null) {
						return;
					}
					if (index == "type") {
						item = this.readableNodeType(item);
					}
					y += "<b>" + this.readableIndex(index) + ":</b> " + (item == null ? "prázdný" : item);
				}
				x.push(y);
			});
			return x;
		}

		private readableIndex(index) {
			var x = {
				room: "Místnost",
				type: "Typ bodu",
				name: "Popisek",
				fromFloor: "Z patra",
				toFloor: "Do patra",
				toBuilding: "Do budovy"
			};
			return x[index];
		}

		private readableNodeType(index) {
			if (this.options.nodeTypes[index]) {
				return this.options.nodeTypes[index].legend;
			}
			return index;
		}

	}
}
