var erro_comum = function(form, mensagem, titulo, foco) {
	$.guaycuru.confirmacao(mensagem);
};

var erro_comum_esconder = function(mensagem, titulo, foco) {
	//$("#last-alert").remove();
};

var Logout = function() {
	$.post(CONFIG_URL + 'ajax/login.php', {logout: 1}, function() {
		document.location = CONFIG_URL;
	});
	return false;
};

$(document).ready(function() {
	// Logout
	$(".link_logout").click(Logout);

	// Auto Forms
	$("form.auto-form").each(function() {
		$(this).validate({
			submitHandler: function(form) {
				$(form).find('button').prop('disabled', true);
				var parse_res = function(res) {
					if((typeof res !== 'object') || (res === null))
						res.ok = false;
					if(res.ok) {
						var msg = ($(form).data('sucesso')) ? $(form).data('sucesso') : 'Dados salvos com sucesso!';
						if(msg !== ' ')
							alert(msg);
						if(!$(form).data('destino')) {
							var destino = document.URL;
							if(destino.charAt(destino.length-1) !== '/')
								destino += '/';
							destino += res.id;
						} else {
							var destino = $(form).data('destino').replace('#ID#', res.id);
						}
						document.location = destino;
					} else {
						if(res.erro)
							var msg = res.erro;
						else if(res.error)
							var msg = res.error;
						else
							var msg = 'Um erro ocorreu, por favor tente novamente.';
						erro_comum($(form), msg, 'Erro:', true);
						$(form).find('button').prop('disabled', false);
					}
				};
				var multipart = ($(form).find('input[type=file]').length > 0);
				erro_comum_esconder();
				if(!multipart) {
					$.post($(form).attr('action'), $(form).serialize(), parse_res)
						.fail(function () {
							parse_res(false);
						});
				} else {
					var iframe_name = 'controle';
					$("#" + iframe_name).unbind('load');
					$("#" + iframe_name).bind('load', function() {
						try {
							parse_res($.parseJSON($(this).contents().text()));
						} catch(err) {
							parse_res({ok: false});
						}
					});
					$(form).attr({
						enctype: "multipart/form-data",
						encoding: "multipart/form-data",
						target: iframe_name
					}).get(0).submit();
				}
			},
			invalidHandler: function(event, validator) {
				// 'this' refers to the form
				var errors = validator.numberOfInvalids();
				if(errors) {
					var msg = (errors === 1)
						? 'Por favor verifique o campo destacado.'
						: 'Por favor verifique os ' + errors + ' campos destacados.';
					erro_comum($(this), msg);
				} else {
					erro_comum_esconder();
				}
			}
		});
	});
});

jQuery.extend(jQuery.validator.messages, {
	required: "Este campo é obrigatório.",
	remote: "Por favor corrija este campo.",
	email: "Por favor digite um endereço de email válido.",
	url: "Por favor digite uma URL válida.",
	date: "Por favor digite uma data válida.",
	dateISO: "Por favor digite uma data válida (ISO).",
	number: "Por favor digite um número válido.",
	digits: "Por favor digite somente digitos.",
	creditcard: "Por favor digite um número de cartão de crédito válido.",
	equalTo: "Por favor digite o mesmo valor novamente.",
	accept: "Por favor digite um valor com uma extensão válida.",
	maxlength: jQuery.validator.format("Por favor não digite mais de {0} caracteres."),
	minlength: jQuery.validator.format("Por favor digite pelo menos {0} caracteres."),
	rangelength: jQuery.validator.format("Por favor digite um valor entre {0} e {1} caracteres."),
	range: jQuery.validator.format("Por favor digite um valor entre {0} e {1}."),
	max: jQuery.validator.format("Por favor digite um valor menor ou igual a {0}."),
	min: jQuery.validator.format("Por favor digite um valor maior ou igual a {0}.")
});
