using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.Validation;

public sealed class DataFinanceiraAttribute : ValidationAttribute
{
    public override bool IsValid(object? value) => value is DateTime d && d.Year >= 1000 && d.TimeOfDay == TimeSpan.Zero;
    public DataFinanceiraAttribute() => ErrorMessage = "Informe uma data válida, sem horário.";
}
