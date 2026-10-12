var CRamCalc = 
{
	conditions: {},
	calculations: {},
	properties: {},
	htmls: {},
	titles: {},
	consents: {},
	params: {},
	popups: {},
	
	OnRangeSliderChange: function(item)
	{
		if ($(item).prop("readonly"))
		{
			$(item).val($(item).data("value"));
			return false;
		}
		
		$(item).parent().find('input[type="text"]').val($(item).val());
		$(item).parent().find('.ram-calc__range-min').html($(item).attr('min'));
		$(item).parent().find('.ram-calc__range-max').html($(item).attr('max'));
		CRamCalc.PropertyValueChange(item);
	},
	
	OnRangeSliderMove: function(item)
	{
		if ($(item).prop("readonly"))
		{
			$(item).val($(item).data("value"));
			return false;
		}
		
		$(item).parent().find('input[type="text"]').val($(item).val());
	},
	
	OnRangeInputChange: function(item)
	{
		$(item).parentsUntil(".ram-calc__range").last().parent().find('input[type="range"]').val($(item).val()).change();
	},
	
	OnNumberInput: function(item)
	{
		var value = $(item).val();
		value = value.replace(new RegExp(/,/g), ".");
		if (value != "-" && value.length > 0 && value.substr(-1) != ".")
		{
			value = parseFloat(value);
		}
		if (isNaN(value))
		{
			value = "";
		}
		
		$(item).val(value);
	},
	
	OnNumberChange: function(item)
	{
		var min = parseFloat($(item).attr("min"));
		var max = parseFloat($(item).attr("max"));
		var step = parseFloat($(item).attr("step"));
		var value = $(item).val().length ? parseFloat($(item).val()) : 0;
		if (value < min) value = min;
		else if (value > max) value = max;
		else if (step)
		{
			var stepPart = value % step;
			if (stepPart > 0)
			{
				if (stepPart > step / 2) value = value - stepPart + step;
				else value = value - stepPart;
			}
			else if (stepPart < 0)
			{
				if (Math.abs(stepPart) > step / 2) value = value - stepPart - step;
				else value = value - stepPart;
			}
		}
		$(item).val(value);
		CRamCalc.PropertyValueChange(item);
	},
	
	OnSelectChange: function(item)
	{
		CRamCalc.PropertyValueChange(item);
	},
	
	OnListItemClick: function(item)
	{
		if ($(item).prop("readonly")) return false;
		
		CRamCalc.PropertyValueChange(item);
	},
	
	PropertyValueChange: function(item)
	{
		var id = $(item).parentsUntil(".ram-calc__calculator-wrap").last().attr("data-calculator");
		
		CRamCalc.UpdatePropertiesValues(id);
		CRamCalc.DoCalculates(id);
		CRamCalc.CheckConditions(id);
		CRamCalc.UpdatePropertiesValues(id);
		CRamCalc.DoCalculates(id);
	},
	
	UpdatePropertiesValues: function(calculatorID)
	{
		if (CRamCalc.properties[calculatorID].length < 1) return false;
		
		for (var i in CRamCalc.properties[calculatorID])
		{
			var property = CRamCalc.properties[calculatorID][i];
			
			property.FORMULA_VALUES = {};
			
			if (property.CODE == "base")
			{
				property.FORMULA_VALUES["["+property.CODE+".price]"] = parseFloat(property.PRICE);
				if (!property.FORMULA_VALUES["["+property.CODE+".price]"]) property.FORMULA_VALUES["["+property.CODE+".price]"] = 0;
			}
			else if (property.TYPE == "number")
			{
				if (!$("[data-calculator='"+calculatorID+"'] [name='"+property.FIELD_NAME+"']").prop("disabled"))
				{
					property.FORMULA_VALUES["["+property.CODE+".value]"] = parseFloat($("[data-calculator='"+calculatorID+"'] [name='"+property.FIELD_NAME+"']").val());
					if (!property.FORMULA_VALUES["["+property.CODE+".value]"]) property.FORMULA_VALUES["["+property.CODE+".value]"] = 0;
				}
				else
				{
					property.FORMULA_VALUES["["+property.CODE+".value]"] = 0;
				}
				property.FORMULA_VALUES["["+property.CODE+".min]"] = parseFloat($("[data-calculator='"+calculatorID+"'] [name='"+property.FIELD_NAME+"']").attr("min"));
				if (!property.FORMULA_VALUES["["+property.CODE+".min]"]) property.FORMULA_VALUES["["+property.CODE+".min]"] = 0;
				property.FORMULA_VALUES["["+property.CODE+".max]"] = parseFloat($("[data-calculator='"+calculatorID+"'] [name='"+property.FIELD_NAME+"']").attr("max"));
				if (!property.FORMULA_VALUES["["+property.CODE+".max]"]) property.FORMULA_VALUES["["+property.CODE+".max]"] = 0;
				property.FORMULA_VALUES["["+property.CODE+".step]"] = parseFloat($("[data-calculator='"+calculatorID+"'] [name='"+property.FIELD_NAME+"']").attr("step"));
				if (!property.FORMULA_VALUES["["+property.CODE+".step]"]) property.FORMULA_VALUES["["+property.CODE+".step]"] = 0;
			}
			else
			{
				property.FORMULA_VALUES["["+property.CODE+".price]"] = 0;
				property.FORMULA_VALUES["["+property.CODE+".pricesumm]"] = 0;
				// property.FORMULA_VALUES["["+property.CODE+".pricesummhint]"] = 0;
				
				if ($("[data-calculator='"+calculatorID+"'] select[name='"+property.FIELD_NAME+"']").length)
				{
					var propertyItem = $("[data-calculator='"+calculatorID+"'] select[name='"+property.FIELD_NAME+"']");
					
					if ($(propertyItem).prop("readonly"))
					{
						
					}
					
					var propertyItemValue = $(propertyItem).val();
					for (var j in property.VALUES)
					{
						var checked = false;
						if ($.isArray(propertyItemValue))
						{
							for (var k in propertyItemValue)
							{
								if (propertyItemValue[k] == j)
								{
									checked = true;
									break;
								}
							}
						}
						else
						{
							if (propertyItemValue == j) checked = true;
						}
						
						property.FORMULA_VALUES["["+property.CODE+"."+j+"_checked]"] = checked;
						property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"] = parseFloat(property.VALUES[j].PRICE);
						if (!property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"]) property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"] = 0;
						
						if (checked)
						{
							property.FORMULA_VALUES["["+property.CODE+".pricesumm]"] += property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"];
							// property.FORMULA_VALUES["["+property.CODE+".pricesummhint]"] += property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"] * parseFloat(property.VALUES[j].HINT);
							property.FORMULA_VALUES["["+property.CODE+".price]"] = property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"];
						}
					}
				}
				else
				{
					for (var j in property.VALUES)
					{
						var checked = $("[data-calculator='"+calculatorID+"'] [name='"+property.FIELD_NAME+"'][value='"+j+"']").prop("checked") ? true : false;
						property.FORMULA_VALUES["["+property.CODE+"."+j+"_checked]"] = checked;
						property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"] = parseFloat(property.VALUES[j].PRICE);
						if (!property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"]) property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"] = 0;
						if (checked)
						{
							property.FORMULA_VALUES["["+property.CODE+".pricesumm]"] += property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"];
							// property.FORMULA_VALUES["["+property.CODE+".pricesummhint]"] += property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"] * parseFloat(property.VALUES[j].HINT);
							property.FORMULA_VALUES["["+property.CODE+".price]"] = property.FORMULA_VALUES["["+property.CODE+"."+j+"_price]"];
						}
					}
				}
			}
		}
	},
	
	CheckConditions: function(calculatorID)
	{
		if (CRamCalc.conditions[calculatorID].length < 1) return false;
		
		var updatedItems = [];
		
		for (var i in CRamCalc.conditions[calculatorID])
		{
			var condition = CRamCalc.conditions[calculatorID][i];
			
			if (condition.FORMATTED_FORMULA.length)
			{
				var formula = condition.FORMATTED_FORMULA;
				
				console.log(formula);
				
				var properties = formula.match(/(\[.*?\])/g);
				
				if (properties && properties.length)
				{
					for (var j in properties)
					{
						var property = properties[j];
						var propertySplit = property.replace("[", "").replace("]", "").split(".");
						
						for (var k in CRamCalc.properties[calculatorID])
						{
							if (CRamCalc.properties[calculatorID][k].CODE == propertySplit[0])
							{
								if (CRamCalc.properties[calculatorID][k].TYPE == 'list' && property.search('_checked') != -1)
								{
									var calculateValue = CRamCalc.properties[calculatorID][k].FORMULA_VALUES[property] ? true : false;
								}
								else
								{
									var calculateValue = CRamCalc.properties[calculatorID][k].FORMULA_VALUES[property];
								}
								
								formula = formula.replace(property, calculateValue);
								break;
							}
						}
					}
				}
				
				var calculations = formula.match(/(calculationstart_[0-9]+_calculationend)/g);
				
				if (calculations && calculations.length)
				{
					for (var j in calculations)
					{
						var calcValue = calculations[j].split("_");
						var calc = null;
						
						for (var p in CRamCalc.calculations[calculatorID])
						{
							if (CRamCalc.calculations[calculatorID][p].CODE == calcValue[1])
							{
								calc = CRamCalc.calculations[calculatorID][p];
								break;
							}
						}
						
						if (calc)
						{
							formula = formula.replace(calculations[j], calc.RESULT);
						}
						else
						{
							formula = formula.replace(calculations[j], "");
						}
					}
				}
				
				console.log(formula);
				
				var result = false;
				
				try
				{
					result = eval(formula);
				}
				catch (error)
				{
					continue;
				}
				
				var actioinsList = null;
				
				if (result)
				{
					if (condition.ACTIONS_TRUE && condition.ACTIONS_TRUE.length > 0) actioinsList = condition.ACTIONS_TRUE;
				}
				else
				{
					if (condition.ACTIONS_FALSE && condition.ACTIONS_FALSE.length > 0) actioinsList = condition.ACTIONS_FALSE;
				}
				
				if (condition.PREV_RESULT != null && result == condition.PREV_RESULT)
				{
					continue;
				}
				else
				{
					CRamCalc.conditions[calculatorID][i].PREV_RESULT = result;
				}
				
				if (actioinsList && actioinsList.length > 0)
				{
					for (var actionIndex in actioinsList)
					{
						var action = actioinsList[actionIndex].split("_");
						
						if (action[0].search("calculations") == 0)
						{
							var actionCalculation = $("[data-calculator='"+calculatorID+"'] [data-calculation]").parent();
							switch (action[1])
							{
								case "show":
								{
									$(actionCalculation).removeAttr("data-hidden");
									break;
								}
								case "hide":
								{
									$(actionCalculation).attr("data-hidden", true);
									break;
								}
								case "enable":
								{
									for (var c in CRamCalc.calculations[calculatorID])
									{
										CRamCalc.calculations[calculatorID][c]["ACTIVE"] = "Y";
									}
									break;
								}
								case "disable":
								{
									for (var c in CRamCalc.calculations[calculatorID])
									{
										CRamCalc.calculations[calculatorID][c]["ACTIVE"] = "N";
									}
									break;
								}
							}
						}
						else if (action[0].search("calculation") == 0)
						{
							var calculationCode = action[0].replace("calculation", "");
							var actionCalculation = $("[data-calculator='"+calculatorID+"'] [data-calculation='"+calculationCode+"']").parent();
							switch (action[1])
							{
								case "show":
								{
									$(actionCalculation).removeAttr("data-hidden");
									break;
								}
								case "hide":
								{
									$(actionCalculation).attr("data-hidden", true);
									break;
								}
								case "enable":
								{
									for (var c in CRamCalc.calculations[calculatorID])
									{
										if (CRamCalc.calculations[calculatorID][c]["CODE"] == calculationCode)
										{
											CRamCalc.calculations[calculatorID][c]["ACTIVE"] = "Y";
											break;
										}
									}
									break;
								}
								case "disable":
								{
									for (var c in CRamCalc.calculations[calculatorID])
									{
										if (CRamCalc.calculations[calculatorID][c]["CODE"] == calculationCode)
										{
											CRamCalc.calculations[calculatorID][c]["ACTIVE"] = "N";
											break;
										}
									}
									break;
								}
							}
						}
						else
						{
							var actionProperty = $("[data-calculator='"+calculatorID+"'] [data-property='"+action[0]+"']");
							var actionPropertyValue = null;
							
							if (action[2])
							{
								actionPropertyValue = $(actionProperty).find("[value='"+action[2]+"']");
							}
							
							switch (action[1])
							{
								/* property */
								case "show": $(actionProperty).removeAttr("data-hidden"); break;
								case "hide": $(actionProperty).attr("data-hidden", true); break;
								case "enable":
								case "disable": $(actionProperty).find("select, input").prop("disabled", action[1] == "disable").change(); break;
								case "readonly":
								{
									if ($(actionProperty).find(".ram-calc__property-list").length)
									{
										if ($(actionProperty).find("select").length)
										{
											if (!$(actionProperty).find("select").prop("readonly"))
											{
												$(actionProperty).append("<input class='ram-calc__property-list-value' type='text' name='"+$(actionProperty).find("select").attr("name")+"' value='"+$(actionProperty).find("select").val()+"'/>")
												
												$(actionProperty).find("select").prop("readonly", true).prop("disabled", true);
											}
										}
										else
										{
											$(actionProperty).attr("readonly", true);
											$(actionProperty).find("input").attr("onclick", "return false;");
										}
									}
									else
									{
										$(actionProperty).find("input").attr("readonly", true);
									}
									
									break;
								}
								case "unreadonly":
								{
									if ($(actionProperty).find(".ram-calc__property-list").length)
									{
										if ($(actionProperty).find("select").length)
										{
											if ($(actionProperty).find("select").prop("readonly"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").remove();
												
												$(actionProperty).find("select").prop("readonly", false).prop("disabled", false);
											}
										}
										else
										{
											$(actionProperty).find("input").removeAttr("readonly").removeAttr("onclick");
										}
									}
									else
									{
										$(actionProperty).find("input").removeAttr("readonly");
									}
									
									break;
								}
								
								/* number */
								case "value":
								{
									if ($(actionProperty).find("input").val() != action[2])
									{
										$(actionProperty).find("input").val(action[2]).change();
									}
									
									break;
								}
								case "min":
								case "max":
								case "step":
								{
									if ($(actionProperty).find("input").attr(action[1]) != action[2])
									{
										$(actionProperty).find("input").attr(action[1], action[2]).change();
									}
									
									break;
								}
								case "conditionvalue":
								{
									if ($(actionProperty).find("input").val() != result)
									{
										$(actionProperty).find("input").val(result).change();
									}
									
									break;
								}
								case "conditionmin":
								case "conditionmax":
								case "conditionstep":
								{
									if ($(actionProperty).find("input").attr(action[1].replace("condition", "")) != result)
									{
										$(actionProperty).find("input").attr(action[1].replace("condition", ""), result).change();
									}
									
									break;
								}
								
								/* list value */
								case "showValue":
								{
									if ($(actionProperty).find("select").length)
									{
										if ($(actionPropertyValue).attr("data-hidden"))
										{
											$(actionPropertyValue).removeAttr("data-hidden");
										}
									}
									else
									{
										if ($(actionPropertyValue).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden"))
										{
											$(actionPropertyValue).parentsUntil(".ram-calc__value").last().parent().removeAttr("data-hidden");
										}
									}
									
									break;
								}
								case "hideValue":
								{
									if ($(actionProperty).find("select").length)
									{
										if (!$(actionPropertyValue).attr("data-hidden"))
										{
											$(actionPropertyValue).attr("data-hidden", true);
										}
									}
									else
									{
										if (!$(actionPropertyValue).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden"))
										{
											$(actionPropertyValue).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden", true);
										}
									}
									
									break;
								}
								case "valueValue":
								{
									if ($(actionProperty).find("select").length)
									{
										if (!$(actionPropertyValue).prop("selected"))
										{
											$(actionPropertyValue).prop("selected", true);
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										if (!$(actionPropertyValue).prop("checked"))
										{
											$(actionPropertyValue).prop("checked", true).change();
										}
									}
									
									break;
								}
								case "unvalueValue":
								{
									if ($(actionProperty).find("select").length)
									{
										if ($(actionPropertyValue).prop("selected"))
										{
											$(actionPropertyValue).prop("selected", false);
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										if ($(actionPropertyValue).prop("checked"))
										{
											$(actionPropertyValue).prop("checked", false).change();
										}
									}
									
									break;
								}
								case "enableValue":
								{
									if ($(actionProperty).find("select").length)
									{
										if ($(actionPropertyValue).prop("disabled"))
										{
											$(actionPropertyValue).prop("disabled", false);
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										if ($(actionPropertyValue).prop("disabled"))
										{
											$(actionPropertyValue).prop("disabled", false).change();
										}
									}
									
									break;
								}
								case "disableValue":
								{
									if ($(actionProperty).find("select").length)
									{
										if (!$(actionPropertyValue).prop("disabled"))
										{
											$(actionPropertyValue).prop("disabled", true);
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										if (!$(actionPropertyValue).prop("disabled"))
										{
											$(actionPropertyValue).prop("disabled", true).change();
										}
									}
									
									break;
								}
								
								/* list all values */
								case "showAllValues":
								{
									if ($(actionProperty).find("select").length)
									{
										$(actionProperty).find("option").each(function()
										{
											if ($(this).attr("data-hidden"))
											{
												$(this).removeAttr("data-hidden");
											}
										});
									}
									else
									{
										$(actionProperty).find("input").each(function()
										{
											if ($(this).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden"))
											{
												$(this).parentsUntil(".ram-calc__value").last().parent().removeAttr("data-hidden");
											}
										});
									}
									
									break;
								}
								case "hideAllValues":
								{
									if ($(actionProperty).find("select").length)
									{
										$(actionProperty).find("option").each(function()
										{
											if (!$(this).attr("data-hidden"))
											{
												$(this).attr("data-hidden", true);
											}
										});
									}
									else
									{
										$(actionProperty).find("input").each(function()
										{
											if (!$(this).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden"))
											{
												$(this).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden", true);
											}
										});
									}
									
									break;
								}
								case "valueAllValues":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option").each(function()
										{
											if (!$(this).prop("selected"))
											{
												$(this).prop("selected", true);
												change = true;
											}
										});
										
										if (change)
										{
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input").each(function()
										{
											if (!$(this).prop("checked"))
											{
												$(this).prop("checked", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "unvalueAllValues":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option").each(function()
										{
											if ($(this).prop("selected"))
											{
												$(this).prop("selected", false);
												change = true;
											}
										});
										
										if (change)
										{
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input").each(function()
										{
											if ($(this).prop("checked"))
											{
												$(this).prop("checked", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "enableAllValues":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option").each(function()
										{
											if ($(this).prop("disabled"))
											{
												$(this).prop("disabled", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input").each(function()
										{
											if ($(this).prop("disabled"))
											{
												$(this).prop("disabled", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "disableAllValues":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option").each(function()
										{
											if (!$(this).prop("disabled"))
											{
												$(this).prop("disabled", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input").each(function()
										{
											if (!$(this).prop("disabled"))
											{
												$(this).prop("disabled", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								
								/* list number value */
								case "showNumberValue":
								{
									if ($(actionProperty).find("select").length)
									{
										$(actionProperty).find("option:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if ($(this).attr("data-hidden"))
											{
												$(this).removeAttr("data-hidden");
											}
										});
									}
									else
									{
										$(actionProperty).find("input:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if ($(this).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden"))
											{
												$(this).parent().removeAttr("data-hidden");
											}
										});
									}
									
									break;
								}
								case "hideNumberValue":
								{
									if ($(actionProperty).find("select").length)
									{
										$(actionProperty).find("option:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if (!$(this).attr("data-hidden"))
											{
												$(this).attr("data-hidden", true);
											}
										});
									}
									else
									{
										$(actionProperty).find("input:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if (!$(this).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden"))
											{
												$(this).parentsUntil(".ram-calc__value").last().parent().attr("data-hidden", true);
											}
										});
									}
									
									break;
								}
								case "valueNumberValue":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if (!$(this).prop("selected"))
											{
												$(this).prop("selected", true);
												change = true;
											}
										});
										
										if (change)
										{
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if (!$(this).prop("checked"))
											{
												$(this).prop("checked", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "unvalueNumberValue":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if ($(this).prop("selected"))
											{
												$(this).prop("selected", false);
												change = true;
											}
										});
										
										if (change)
										{
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if ($(this).prop("checked"))
											{
												$(this).prop("checked", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "enableNumberValue":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if ($(this).prop("disabled"))
											{
												$(this).prop("disabled", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if ($(this).prop("disabled"))
											{
												$(this).prop("disabled", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "disableNumberValue":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if (!$(this).prop("disabled"))
											{
												$(this).prop("disabled", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find("input:eq("+(action[2] > 0 ? action[2] - 1 : action[2])+")").each(function()
										{
											if (!$(this).prop("disabled"))
											{
												$(this).prop("disabled", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								
								/* list value with */								
								case "showValueWith":
								{
									if ($(actionProperty).find("select").length)
									{
										$(actionProperty).find("option:contains('"+action[2]+"')").each(function()
										{
											if ($(this).attr("data-hidden"))
											{
												$(this).removeAttr("data-hidden");
											}
										});
									}
									else
									{
										$(actionProperty).find(".ram-calc__value:contains('"+action[2]+"')").each(function()
										{
											if ($(this).attr("data-hidden"))
											{
												$(this).removeAttr("data-hidden");
											}
										});
									}
									
									break;
								}
								case "hideValueWith":
								{
									if ($(actionProperty).find("select").length)
									{
										$(actionProperty).find("option:contains('"+action[2]+"')").each(function()
										{
											if (!$(this).attr("data-hidden"))
											{
												$(this).attr("data-hidden", true);
											}
										});
									}
									else
									{
										$(actionProperty).find(".ram-calc__value:contains('"+action[2]+"')").each(function()
										{
											if (!$(this).attr("data-hidden"))
											{
												$(this).attr("data-hidden", true);
											}
										});
									}
									
									break;
								}
								case "valueValueWith":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:contains('"+action[2]+"')").each(function()
										{
											if (!$(this).prop("selected"))
											{
												$(this).prop("selected", true);
												change = true;
											}
										});
										
										if (change)
										{
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find(".ram-calc__value:contains('"+action[2]+"')").each(function()
										{
											if (!$(this).find("input").prop("checked"))
											{
												$(this).find("input").prop("checked", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "unvalueValueWith":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:contains('"+action[2]+"')").each(function()
										{
											if ($(this).prop("selected"))
											{
												$(this).prop("selected", false);
												change = true;
											}
										});
										
										if (change)
										{
											if ($(actionProperty).find(".ram-calc__property-list-value"))
											{
												$(actionProperty).find(".ram-calc__property-list-value").val($(actionProperty).find("select").val());
											}
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find(".ram-calc__value:contains('"+action[2]+"')").each(function()
										{
											if ($(this).find("input").prop("checked"))
											{
												$(this).find("input").prop("checked", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "enableValueWith":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:contains('"+action[2]+"')").each(function()
										{
											if ($(this).prop("disabled"))
											{
												$(this).prop("disabled", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find(".ram-calc__value:contains('"+action[2]+"')").each(function()
										{
											if ($(this).find("input").prop("disabled"))
											{
												$(this).find("input").prop("disabled", false);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
								case "disableValueWith":
								{
									if ($(actionProperty).find("select").length)
									{
										var change = false;
										
										$(actionProperty).find("option:contains('"+action[2]+"')").each(function()
										{
											if (!$(this).prop("disabled"))
											{
												$(this).prop("disabled", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("select").change();
										}
									}
									else
									{
										var change = false;
										
										$(actionProperty).find(".ram-calc__value:contains('"+action[2]+"')").each(function()
										{
											if (!$(this).find("input").prop("disabled"))
											{
												$(this).find("input").prop("disabled", true);
												change = true;
											}
										});
										
										if (change)
										{
											$(actionProperty).find("input").first().change();
										}
									}
									
									break;
								}
							}
						}
					}
				}
			}
		}
	},
	
	DoCalculates: function(calculatorID)
	{
		if (CRamCalc.calculations[calculatorID].length < 1) return false;
		
		for (var i in CRamCalc.calculations[calculatorID])
		{
			var calculation = CRamCalc.calculations[calculatorID][i];
			
			if (calculation.FORMATTED_FORMULA.length)
			{
				var formula = calculation.FORMATTED_FORMULA;
				
				var properties = formula.match(/(\[.*?\])/g);
				
				if (properties && properties.length)
				{
					for (var j in properties)
					{
						var property = properties[j];
						var propertySplit = property.replace("[", "").replace("]", "").split(".");
						
						for (var k in CRamCalc.properties[calculatorID])
						{
							if (CRamCalc.properties[calculatorID][k].CODE == propertySplit[0])
							{
								formula = formula.replace(property, CRamCalc.properties[calculatorID][k].FORMULA_VALUES[property]);
								break;
							}
						}
					}
				}
				
				var calculations = formula.match(/(calculationstart_[0-9]+_calculationend)/g);
				
				if (calculations && calculations.length)
				{
					for (var j in calculations)
					{
						var calcValue = calculations[j].split("_");
						var calc = null;
						
						for (var p in CRamCalc.calculations[calculatorID])
						{
							if (CRamCalc.calculations[calculatorID][p].CODE == calcValue[1])
							{
								calc = CRamCalc.calculations[calculatorID][p];
								break;
							}
						}
						
						if (calc)
						{
							formula = formula.replace(calculations[j], calc.RESULT);
						}
						else
						{
							formula = formula.replace(calculations[j], "");
						}
					}
				}
				
				var result = 0;
				
				try
				{
					result = eval(formula);
				}
				catch (error)
				{
					result = "0";
				}
				
				calculation.ROUNDING = parseFloat(calculation.ROUNDING);
				
				if (!isNaN(calculation.ROUNDING) && isFinite(calculation.ROUNDING))
				{
					if (calculation.ROUNDING >= 0)
					{
						if (calculation.ROUNDING > 100) calculation.ROUNDING = 100;
					
						result = result.toFixed(calculation.ROUNDING);
					}
					else
					{
						var order = Math.pow(10, Math.abs(calculation.ROUNDING));
						
						result = Math.round(result / order) * order;
					}
				}
				
				CRamCalc.calculations[calculatorID][i].RESULT = result;
				$("[data-calculator='"+calculatorID+"'] [data-calculation='"+calculation.CODE+"']").html(result);
			}
			else
			{
				CRamCalc.calculations[calculatorID][i].RESULT = 0;
				$("[data-calculator='"+calculatorID+"'] [data-calculation='"+calculation.CODE+"']").html("0");
			}
		}
		
		CRamCalc.UpdateTotalCalculates($("[data-calculator='"+calculatorID+"']").parent().attr("data-calculator-wrap"));
	},
	
	UpdateTotalCalculates: function(calculatorWrapID)
	{
		if ($("[data-calculator-wrap='"+calculatorWrapID+"'] .ram-calc__calculator-total-calculations").length)
		{
			var total = $("[data-calculator-wrap='"+calculatorWrapID+"'] .ram-calc__calculator-total-calculations");
			$(total).find("[data-calculation]").each(function()
			{
				var calculationCode = $(this).attr("data-calculation");
				var totalPrice = 0;
				var calculation = null;
				
				for (var i in CRamCalc.calculations[calculatorWrapID])
				{
					if (CRamCalc.calculations[calculatorWrapID][i].CODE == calculationCode)
					{
						calculation = CRamCalc.calculations[calculatorWrapID][i];
						break;
					}
				}
				
				if ($(".ram-calc__calculator-calculations [data-calculation='"+calculationCode+"']").length == 1)
				{
					$(total).attr("data-hidden", true);
				}
				else
				{
					$(total).removeAttr("data-hidden");
				}
				
				$(".ram-calc__calculator-calculations [data-calculation='"+calculationCode+"']").each(function()
				{
					totalPrice += parseFloat($(this).html());
				});
				
				if (!isNaN(calculation.ROUNDING) && isFinite(calculation.ROUNDING))
				{
					if (calculation.ROUNDING >= 0)
					{
						if (calculation.ROUNDING > 100) calculation.ROUNDING = 100;
					
						totalPrice = totalPrice.toFixed(calculation.ROUNDING);
					}
					else
					{
						var order = Math.pow(10, Math.abs(calculation.ROUNDING));
						
						totalPrice = Math.round(totalPrice / order) * order;
					}
				}
				
				$(this).html(totalPrice);
			});
		}
	},
	
	SelectCalculator: function(item)
	{
		var calculatorWrap = $(item).parentsUntil(".ram-calc__calculator-wrap").last().parent();
		var calculatorTabs = $(item).parent();
		var index = $(item).attr("data-index");
		
		$(calculatorTabs).find("[data-index]").removeClass("ram-calc__tab_active");
		$(item).addClass("ram-calc__tab_active");
		
		$(calculatorWrap).find("[data-calculator]").addClass("ram-calc__calculator_unactive");
		$(calculatorWrap).find("[data-calculator][data-index='"+index+"']").removeClass("ram-calc__calculator_unactive");
	},
	
	DeleteCalculator: function(item)
	{
		var calculatorWrap = $(item).parentsUntil(".ram-calc__calculator-wrap").last().parent();
		var index = $(item).parent().attr("data-index");
		
		if (confirm(BX.message("CONFIRM_CALCULATOR_DELETE")))
		{
			if ($(calculatorWrap).find(".ram-calc__tab[data-index='"+index+"']").hasClass("ram-calc__tab_active"))
			{
				$(calculatorWrap).find(".ram-calc__tab").not(".ram-calc__tab_active").first().click();
			}
			
			$(calculatorWrap).find("[data-calculator][data-index='"+index+"']").remove();
			$(calculatorWrap).find(".ram-calc__tab[data-index='"+index+"']").remove();
			
			CRamCalc.UpdateTotalCalculates($(calculatorWrap).attr("data-calculator-wrap"));
		}
	},
	
	AddCalculator: function(item, id)
	{
		var calculatorWrap = $(item).parentsUntil(".ram-calc__calculator-wrap").last().parent();
		var calculatorTabs = $(item).parent();
		var index = 0;
		$(calculatorWrap).find("[data-calculator]").each(function()
		{
			index = Math.max(parseInt($(this).attr('data-index')), index);
		});
		index++;
		
		var newTab = $("<div class='ram-calc__tab' data-index='"+index+"' onclick='return CRamCalc.SelectCalculator(this);'>"+CRamCalc.titles[id]+" #"+index+"<div class='ram-calc__tab-delete' onclick='return CRamCalc.DeleteCalculator(this);'></div></div>")
		
		$(newTab).insertBefore($(item));
		
		$(CRamCalc.htmls[id]).insertAfter($(calculatorWrap).find("[data-calculator]").last());
		
		$("[data-calculator='"+id+"']").attr("data-calculator", id+"_"+index).attr("data-index", index).addClass("ram-calc__calculator_unactive");
		
		CRamCalc.AddData(
		{
			id: id+"_"+index,
			conditions: $.extend(true, {}, CRamCalc.conditions[id]),
			calculations: $.extend(true, {}, CRamCalc.calculations[id]),
			properties: $.extend(true, {}, CRamCalc.properties[id]),
		});
		
		$(newTab).click();
	},
	
	AddData: function(params)
	{
		if (!params.id) return false;
		
		var id = params.id;
		
		CRamCalc.params[id] = params.params;
		
		if (params.multyple)
		{
			CRamCalc.conditions[id] = params.conditions;
			CRamCalc.calculations[id] = params.calculations;
			CRamCalc.properties[id] = params.properties;
			
			CRamCalc.htmls[id] = $("[data-calculator='"+id+"']").clone()[0].outerHTML;
			CRamCalc.titles[id] = params.title;
			
			$("[data-calculator='"+id+"']").attr("data-calculator", id+"_1").attr("data-index", 1);
			id += "_1";
		}
		
		CRamCalc.conditions[id] = $.extend(true, {}, params.conditions);
		CRamCalc.calculations[id] = $.extend(true, {}, params.calculations);
		CRamCalc.properties[id] = $.extend(true, {}, params.properties);
		
		CRamCalc.UpdatePropertiesValues(id);
		CRamCalc.DoCalculates(id);
		CRamCalc.CheckConditions(id);
		CRamCalc.UpdatePropertiesValues(id);
		CRamCalc.DoCalculates(id);
	},
	
	InitPopup: function(params)
	{
		CRamCalc.popups[params.id] =
		{
			popup: null,
			successMessage: params.successMessage,
			failMessage: params.failMessage,
		};
	},
	
	HidePopup: function(id)
	{
		if (CRamCalc.popups[id].popup)
		{
			$(CRamCalc.popups[id].popup).remove();
			CRamCalc.popups[id].popup = null;
		}
	},
	
	ShowPopup: function(params)
	{
		CRamCalc.HidePopup(params.id);
		
		CRamCalc.popups[params.id].popup = $("<div class='ram-calc__popup'><div class='ram-calc__popup-wrap'><div class='ram-calc__popup-content'></div><a href='#' class='ram-calc__popup-close' onclick='CRamCalc.HidePopup(\""+params.id+"\"); return false;'></a></div></div>");
		
		$(CRamCalc.popups[params.id].popup).find(".ram-calc__popup-content").html(params.status == "success" ? CRamCalc.popups[params.id].successMessage : CRamCalc.popups[params.id].failMessage);
		
		$(".ram-calc__calculator-wrap[data-calculator-wrap='"+params.id+"']").append(CRamCalc.popups[params.id].popup);
	},
	
	Submit: function(item)
	{
		var calculatorWrap = $(item).parentsUntil(".ram-calc__calculator-wrap").last().parent();
		var parentID = $(calculatorWrap).attr("data-calculator-wrap");
		var data = JSON.parse(atob(CRamCalc.params[parentID]));
		
		if (data.ACTION == "request" && data.REQUEST_FIELDS.length > 0)
		{
			var requestFields = {};
			var firstEmpty = null;
			
			for (var i in data.REQUEST_FIELDS)
			{
				var fieldItem = $(calculatorWrap).find(".ram-calc__request-field:eq("+i+")")[0];
				var fieldValue = $(fieldItem).find("input[type='text'],textarea").val();
				
				if (data.REQUEST_FIELDS_REQUIRED.includes(i) && fieldValue.length < 1)
				{
					if (!firstEmpty)
					{
						firstEmpty = i;
					}
					
					$(fieldItem).find(".ram-calc__request-field-error").removeAttr("data-hidden");
				}
				else
				{
					requestFields[data.REQUEST_FIELDS[i]] = fieldValue;
					
					$(fieldItem).find(".ram-calc__request-field-error").attr("data-hidden", true);
				}
			}
			
			if (firstEmpty)
			{
				$(calculatorWrap).find(".ram-calc__request-field:eq("+firstEmpty+")").find("input,textarea").first().focus();
				
				return false;
			}
			
			data.FIELDS = requestFields;
		}
		
		data.CALC = {};
		
		$(calculatorWrap).find("[data-calculator]").each(function()
		{
			var id = $(this).attr("data-calculator");
			data.CALC[id] = {};
			data.CALC[id].VALUES = $(this).find("form").serializeObject().property;
			data.CALC[id].CALCULATIONS = {};
			for (var i in CRamCalc.calculations[id])
			{
				if (CRamCalc.calculations[id][i].ACTIVE == "Y")
				{
					data.CALC[id].CALCULATIONS[CRamCalc.calculations[id][i].CODE] = CRamCalc.calculations[id][i].RESULT;
				}
			}
			data.CALC[id].PARENT_ID = parentID;
		});
		
		if (data.RECAPTCHA == "Y")
		{
			grecaptcha.ready(function()
			{
				grecaptcha.execute(data.RECAPTCHA_CLIENT_KEY, {action: "submit"}).then(function(token)
				{
					data.TOKEN = token;
					
					$.ajax({url: data.AJAX_URL, type: "POST", data: data}).done(function(msg)
					{
						CRamCalc.ShowPopup({id: parentID, status: msg});
					});
				});
			});
		}
		else
		{
			$.ajax({url: data.AJAX_URL, type: "POST", data: data}).done(function(msg)
			{
				CRamCalc.ShowPopup({id: parentID, status: msg});
			});
		}
		
		return false;
	},
	
	OnFileUpload: function(item)
	{
		var calcWrap = $(item).parentsUntil(".ram-calc__calculator-wrap").last().parent();
		var calcID = $(calcWrap).attr("data-calculator-wrap");
		var calcData = JSON.parse(atob(CRamCalc.params[calcID]));
		
		var files = $(item)[0].files;
		var file = files[0];
		var fileField = $(item).parentsUntil(".ram-calc__request-field").last().parent();
		
		if (file)
		{
			if ($(item).attr("data-max-size"))
			{
				if (file.size / 1024 > parseFloat($(item).attr("data-max-size")))
				{
					$(fileField).find(".ram-calc__file-size-error").show();
					$(fileField).find(".ram-calc__file-title").text($(fileField).find(".ram-calc__file-title").attr("data-default"));
					
					$(item).parent().find("input[type='text']").val("");
					
					return false;
				}
			}
		
			$(item).parentsUntil(".ram-calc__request-field").last().parent().find(".ram-calc__file-size-error").hide();
			$(fileField).find(".ram-calc__file-title").text(file.name+" ("+CRamCalc.FileSizeFormat(file.size)+")");
			
			var formData = new FormData();
			formData.append("file", file, file.name);
			
			$.ajax(
			{
				type: "POST",
				url: calcData.UPLOAD_URL,
				success: function(data)
				{
					var fileData = JSON.parse(data);
					$(item).parent().find("input[type='text']").val(fileData.PATH);
				},
				error: function(error)
				{
					console.log(error.statusText);
				},
				async: true,
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				timeout: 60000
			});
		}
		else
		{
			$(fileField).find(".ram-calc__file-title").text($(fileField).find(".ram-calc__file-title").attr("data-default"));
			$(fileField).find(".ram-calc__file-size-error").hide();
			
			$(item).parent().find("input[type='text']").val("");
		}
	},
	
	FileSizeFormat: function(bytes, decimals = 2)
	{
		if (bytes === 0) return "0 B";
		const k = 1024;
		const dm = decimals < 0 ? 0 : decimals;
		const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
	}
};

!function(e,i){if("function"==typeof define&&define.amd)define(["exports","jquery"],function(e,r){return i(e,r)});else if("undefined"!=typeof exports){var r=require("jquery");i(exports,r)}else i(e,e.jQuery||e.Zepto||e.ender||e.$)}(this,function(e,i){function r(e,r){function n(e,i,r){return e[i]=r,e}function a(e,i){for(var r,a=e.match(t.key);void 0!==(r=a.pop());)if(t.push.test(r)){var u=s(e.replace(/\[\]$/,""));i=n([],u,i)}else t.fixed.test(r)?i=n([],r,i):t.named.test(r)&&(i=n({},r,i));return i}function s(e){return void 0===h[e]&&(h[e]=0),h[e]++}function u(e){switch(i('[name="'+e.name+'"]',r).attr("type")){case"checkbox":return"on"===e.value?!0:e.value;default:return e.value}}function f(i){if(!t.validate.test(i.name))return this;var r=a(i.name,u(i));return l=e.extend(!0,l,r),this}function d(i){if(!e.isArray(i))throw new Error("formSerializer.addPairs expects an Array");for(var r=0,t=i.length;t>r;r++)this.addPair(i[r]);return this}function o(){return l}function c(){return JSON.stringify(o())}var l={},h={};this.addPair=f,this.addPairs=d,this.serialize=o,this.serializeJSON=c}var t={validate:/^[a-z_][a-z0-9_]*(?:\[(?:\d*|[a-z0-9_]+)\])*$/i,key:/[a-z0-9_]+|(?=\[\])/gi,push:/^$/,fixed:/^\d+$/,named:/^[a-z0-9_]+$/i};return r.patterns=t,r.serializeObject=function(){return new r(i,this).addPairs(this.serializeArray()).serialize()},r.serializeJSON=function(){return new r(i,this).addPairs(this.serializeArray()).serializeJSON()},"undefined"!=typeof i.fn&&(i.fn.serializeObject=r.serializeObject,i.fn.serializeJSON=r.serializeJSON),e.FormSerializer=r,r});