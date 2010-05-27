/* Copyleft (CC) 2010 Dariusz Karcz <harmer@harmer.pl> */

Ape.registerCmd ("inlinedebug", false, function (params, infos) {
	if (params.password == Ape.config ("inlinedebug.conf", "password")) {
		if ($defined (params.eval)) {

			var ind_result;
			try {
				eval ("ind_result = " + params.eval + "");
				var resultable = ["string", "number", "date", "boolean"];
				var type = $type (ind_result);
				if (!type)
					type = "false";
				var data = {type: type};
				if (resultable.indexOf (type) >= 0)
					data.result = ind_result;

				return {name: "inlinedebug", data: data};
			}
			catch (e) {
				return ["402", "EVAL_ERROR: " + e.message];
			}
		} else {
			return ["401", "EVAL_NOT_DEFINED"];
		}
	} else {
		return ["400", "BAD_PASSWORD"];
	}
})

