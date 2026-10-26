
namespace OdontoCare.Application.DTOs;

public class PacienteResponseDto
{
    public int IdPaciente { get; set; }
    public string Nome { get; set; } = string.Empty;
    public string? Telefone { get; set; }
    public string? Email { get; set; }
    public string? Cidade { get; set; }
    public string? Estado { get; set; }
}
