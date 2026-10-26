namespace OdontoCare.Domain.Entities;

public sealed class Equipamento
{
    public int IdEquipamento { get; set; }
    public string NomeEquipamento { get; set; } = "";
    public string? Descricao { get; set; }
    public string? StatusEquipamento { get; set; }
}
