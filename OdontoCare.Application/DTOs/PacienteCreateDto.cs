
using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.DTOs;

public class PacienteCreateDto
{
    [Required(ErrorMessage = "O nome é obrigatório.")]
    [MaxLength(200)]
    public string Nome { get; set; } = string.Empty;

    [Required(ErrorMessage = "O CPF é obrigatório.")]
    [MaxLength(14)]
    public string Cpf { get; set; } = string.Empty;

    [MaxLength(20)]
    public string? Telefone { get; set; }

    [EmailAddress(ErrorMessage = "Informe um e-mail válido.")]
    [MaxLength(200)]
    public string? Email { get; set; }

    [MaxLength(10)]
    public string? Cep { get; set; }

    [MaxLength(100)]
    public string? Logradouro { get; set; }

    [MaxLength(10)]
    public string? Numero { get; set; }

    [MaxLength(60)]
    public string? Bairro { get; set; }

    [MaxLength(60)]
    public string? Cidade { get; set; }

    [MaxLength(2)]
    public string? Estado { get; set; }

    public DateTime? DataNascimento { get; set; }
    public string? HistoricoOdontologico { get; set; }
    public string? Alergias { get; set; }
    public string? Exames { get; set; }
    public string? TratamentosAnteriores { get; set; }
    public string? ProntuarioClinicoDigital { get; set; }
}
