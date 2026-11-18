using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.Validation;

public sealed class DinheiroAttribute : ValidationAttribute
{
    public override bool IsValid(object? value) => value is decimal d && d >= 0 && d <= 99999999.99m && decimal.Round(d, 2) == d;
    public DinheiroAttribute() => ErrorMessage = "Informe valor entre 0 e 99999999,99 com no máximo duas casas decimais.";
}
