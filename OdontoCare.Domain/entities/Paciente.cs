
namespace OdontoCare.Domain.Entities;

public class Paciente
{
    public int IdPaciente { get; set; }

    public string Nome { get; set; } = string.Empty;

    public string Cpf { get; set; } = string.Empty;

    public string? Telefone { get; set; }

    public string? Email { get; set; }

    public string? Cep { get; set; }

    public string? Logradouro { get; set; }

    public string? Numero { get; set; }

    public string? Bairro { get; set; }

    public string? Cidade { get; set; }

    public string? Estado { get; set; }

    public DateTime? DataNascimento { get; set; }

    public string? HistoricoOdontologico { get; set; }

    public string? Alergias { get; set; }

    public string? Exames { get; set; }

    public string? TratamentosAnteriores { get; set; }

    public string? ProntuarioClinicoDigital { get; set; }
}
